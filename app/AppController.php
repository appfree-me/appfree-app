<?php

declare(strict_types=1);

namespace AppFree;


use AppFree\appfree\modules\MvgRad\MvgRadStateMachine;
use AppFree\appfree\StateMachineContext;
use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelHangupRequest;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\Ari\Interfaces\EventReceiverInterface;
use AppFree\Ari\PhpAri;
use Evenement\EventEmitterInterface;
use Finite\Exception\ObjectException;
use Finite\Exception\TransitionException;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Monolog\Logger;
use Ratchet\Client\WebSocket;
use React\Promise\PromiseInterface;
use Swagger\Client\ApiException;

class AppController implements StatefulInterface, EventReceiverInterface
{
    public PhpAri $ari;
    public PromiseInterface $stasisClient;
    public Logger $logger;
    protected Client $client;
    private array $stateMachines = [];
    private ?string $state = null;
    private EventEmitterInterface $emitter;

    public function __construct(EventEmitterInterface $emitter, PhpAri $phpAri, Logger $stasisLogger, Client $client)
    {
        $this->emitter = $emitter;
        $this->ari = $phpAri;
        $this->logger = $stasisLogger;
        $this->client = $client;
    }

    /**
     * @throws ApiException
     */
    private function denyChannel(string $channelId): void
    {
        $this->logger->error("Channel $channelId denied");
        $this->ari->channels()->play($channelId, ['media:please-try-call-later'], null, null, null, "channel-denied");
        sleep(2);
        $this->ari->channels()->continueInDialplan($channelId);
    }

    /**
     * @throws ApiException
     */
    private function prepareForCall(string $channelId): void
    {
        if (isset($this->stateMachines["$channelId"])) {
            //todo ist sichergestellt, dass das nicht passieren kann?
            $this->logger->error("Channel $channelId already has a state machine, this should never happen.");
            $this->denyChannel($channelId);
            return;
        }

        $sm = $this->initStateMachine(new StateMachineContext($channelId, $this->ari->channels()));
        $this->stateMachines["$channelId"] = $sm;

        $this->logger->notice("Added Channel", [$channelId]);
    }

    private function removeStateMachine(string $channelId): void
    {
        unset($this->stateMachines[$channelId]);
    }

    public function handler(int $signo, mixed $siginfo): void
    {
        switch ($signo) {
            case SIGINT:
                // handle shutdown tasks
                echo "SIGINT caught, endHandler, closing Websocket\n";

                $this->stasisClient->then(function (WebSocket $conn) {
                    $conn->close();
                    exit;
                });
            default:
                // handle all other signals
        }
    }

    /**
     */
    public function start(): void
    {
        $this->emitter->on(PhpAri::EVENT_NAME_APPFREE_MESSAGE, function (AppFreeDto $dto) {
            $this->receive($dto);
        });

        $this->stasisClient = resolve(PromiseInterface::class);
    }

    public function initStateMachine(StateMachineContext $stateMachineContext): StateMachineInterface
    {
        $sm = resolve(MvgRadStateMachine::class);
        $sm->setObject(new StatefulObject());
        $sm->setContext($stateMachineContext);
        $sm->initialize();

        return $sm;
    }

    public function getFiniteState(): ?string
    {
        return $this->state;
    }

    public function setFiniteState($state): void
    {
        $this->state = $state;
    }

    /**
     *
     * Called from PhpAri
     *
     * @throws TransitionException
     * @throws ApiException
     */
    public function receive(AppFreeDto $eventDto): void
    {
        $this->logger->notice("receive " . serialize($eventDto));
        $this->myEvents($eventDto);
    }

    public static function getUserId(string $number): ?int
    {
        $user = DB::table('users')->where('mobilephone', $number)->first();

        // Right now, if the user is available in the database, this counts as authentication
        if( isset($user) && $user->mobilephone === $number) {
            return $user->id;
        }

        return null;
    }

    private function myEvents($eventDto): void
    {
        if ($eventDto instanceof StasisStart) {
            if (!config('app.authenticate') || $this->getUserId($eventDto->channel->caller->number)) {
                $this->prepareForCall($eventDto->channel->id);
            } else {
                // todo: play rejection message
                // later may throw user to "login" state machine
                // todo: implement transitions between state machines
                $this->ari->channels()->hangup($eventDto->channel->id);
                return;
            }
        }

        if ($eventDto instanceof StasisEnd) {
            $this->removeStateMachine($eventDto->channel->id);
        }


        if ($eventDto instanceof ChannelHangupRequest) {
            $this->removeStateMachine($eventDto->channel->id);
        }

        // Initial State

        if (isset($this->xxxx)) {
            $state = $this->xxxx->getCurrentState();
            $this->logger->debug("AppController::myEvents::" . $state->getName() . "::onEvent(" . json_encode($eventDto) . ")");

            $state->onEvent($eventDto);
        } else {
            $this->logger->error("AppController::myEvents:: (State not initialized)::onEvent(" . json_encode($eventDto) . ")");
        }
    }
}
