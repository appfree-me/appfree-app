<?php

declare(strict_types=1);

namespace AppFree;


use AllowDynamicProperties;
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
use Illuminate\Support\Facades\DB;
use Monolog\Logger;
use Ratchet\Client\WebSocket;
use React\Promise\PromiseInterface;
use Swagger\Client\ApiException;

#[AllowDynamicProperties] class AppController implements StatefulInterface, EventReceiverInterface
{
    public PhpAri $ari;
    public PromiseInterface $stasisClient;
    public Logger $logger;
    protected Client $client;
    private array $stasisChannelIDs = [];
    public StateMachineInterface $sm;
    private ?string $state = null;
    private EventEmitterInterface $emitter;

    public function __construct(StateMachineInterface $sm, EventEmitterInterface $emitter, PhpAri $phpAri, Logger $stasisLogger, Client $client)
    {
        $this->sm = $sm;
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
    private function addChannel(string $channelId): void
    {
        if (count($this->stasisChannelIDs)) { // todo: allow multiple channels
            $this->denyChannel($channelId);
            return;
        }
        $this->stasisChannelIDs[] = $channelId;
        $this->logger->notice("Added Channel", [$channelId]);
    }

    public function getChannelID(): ?string
    {
        return $this->stasisChannelIDs[0] ?? null;
    }

    private function removeChannel($id): void
    {
        $this->stasisChannelIDs = array_diff($this->stasisChannelIDs, [$id]);
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
     * @throws ObjectException
     */
    public function start(): void
    {
        $this->sm->setObject($this);
        $this->sm->initialize();

        $this->emitter->on(PhpAri::EVENT_NAME_APPFREE_MESSAGE, function (AppFreeDto $dto) {
            $this->receive($dto);
        });

        $this->stasisClient = resolve(PromiseInterface::class);
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
     * Called from phpari
     *
     * @throws TransitionException
     * @throws ApiException
     */
    public function receive(AppFreeDto $eventDto): void
    {
        $this->logger->notice("receive " . serialize($eventDto));
        $this->myEvents($eventDto);
    }

    private function authenticate(string $number)
    {
        $user = DB::table('users')->where('mobilephone', $number)->first();

        // Right now, if the user is available in the database, this counts as authentication
        return isset($user) && $user->mobilephone === $number;
    }

    private function myEvents($eventDto): void
    {
        if ($eventDto instanceof StasisStart) {
            if (!config('app.authenticate') || $this->authenticate($eventDto->channel->caller->number)) {
                $this->addChannel($eventDto->channel->id);
            } else {
                // todo: play rejection message
                // later may throw user to "login" state machine
                // todo: implement transitions between state machines
                $this->ari->channels()->hangup($eventDto->channel->id);
                return;
            }
        }

        if ($eventDto instanceof StasisEnd) {
            $this->removeChannel($eventDto->channel->id);
        }


        if ($eventDto instanceof ChannelHangupRequest) {
            $this->removeChannel($eventDto->channel->id);
        }

        // Initial State
        /** @var \AppFree\appfree\modules\MvgRad\Interfaces\AppFreeStateInterface $state */
        $state = $this->sm->getCurrentState();
        $this->logger->debug("myEvents State " . $state->getName() . "::onEvent(" . json_encode($eventDto) . ")");

        $state->onEvent($eventDto);
    }
}
