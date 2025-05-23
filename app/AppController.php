<?php

declare(strict_types=1);

namespace AppFree;

use App\Models\User;
use App\ReactWebsocketInterface;
use AppFree\appfree\modules\MvgRad\MvgRadStateMachine;
use AppFree\appfree\StateMachineContext;
use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelHangupRequest;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Caller;
use AppFree\Ari\Interfaces\EventReceiverInterface;
use AppFree\Ari\PhpAri;
use AppFree\Watchdog\WatchdogController;
use Evenement\EventEmitterInterface;
use Finite\Exception\TransitionException;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use GuzzleHttp\Client;
use Monolog\Logger;
use Ratchet\Client\WebSocket;
use React\Promise\PromiseInterface;
use Swagger\Client\ApiException;

/**
 * Suppress all warnings from these two rules.
 *
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
class AppController implements StatefulInterface, EventReceiverInterface
{
    public PromiseInterface|ReactWebsocketInterface $wsClient; //fixme typehint
    private array $stateMachines = [];
    private ?string $state = null;

    public function __construct(
        private readonly EventEmitterInterface $emitter,
        private readonly PhpAri                $ari,
        public Logger                          $logger,
        protected Client                       $client,
        private readonly WatchdogController    $watchdogController
    ) {
    }

    /**
     * @throws ApiException
     */
    private function denyChannel(string $channelId): void
    {
        $this->logger->notice("Channel $channelId denied");
        $this->ari->channels()->hangup($channelId);
    }

    public function removeStateMachine(string $channelId): void
    {
        unset($this->stateMachines[$channelId]);
    }

    public function handler(int $signo, mixed $siginfo): void
    {
        switch ($signo) {
            case SIGINT:
                // handle shutdown tasks
                $this->logger->notice("SIGINT caught. Shutting down...");
                exit;
            default:
                // handle all other signals
        }
    }

    public function shutdown(): void
    {
        $this->wsClient->then(function (WebSocket $conn) {
            $this->logger->notice("Closing Websocket...");
            $conn->close();
            exit;
        });
    }

    public function start(): void
    {
        $this->wsClient = resolve(ReactWebsocketInterface::class);
        $this->setupAsteriskEvents();

        if (config('watchdog.internal')) {
            $this->setupWatchdog();
        }

        $this->dropAllCalls();
    }

    private function dropAllCalls()
    {
        $r = $this->ari->channels()->callListWithHttpInfo();
        if (!$r) {
            return;
        }
        $x = json_decode($r[0], true);
        foreach ($x as $c) {
            $this->logger->debug("Cleaning up old channel " . $c["id"]);
            $this->denyChannel($c["id"]);
        }
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
        $this->receiveStasisEvent($eventDto);
    }

    public static function getUserForPhonenumber(string $number): ?User
    {
        $user = User::where('mobilephone', $number)->first();

        // Right now, if the user is available in the database, this counts as authentication
        if (isset($user) && $user->mobilephone === $number) {
            return $user;
        }

        resolve(Logger::class)->debug("User $number not found.");

        return null;
    }

    /**
     * @throws ApiException
     */
    private function prepareForCall(string $channelId, Caller $caller, ?User $user): void
    {
        if (isset($this->stateMachines["$channelId"])) {
            //todo ist sichergestellt, dass das nicht passieren kann?
            $this->logger->error("Channel $channelId already has a state machine, this should never happen.");
            $this->denyChannel($channelId);
            return;
        }

        $sm = $this->initStateMachine(new StateMachineContext($channelId, $this->ari->channels(), $caller, $user));
        $this->stateMachines["$channelId"] = $sm;

        $this->logger->notice("Added Channel", [$channelId]);
    }

    //    private function receiveWatchdogEvent(WatchdogExecuteApiCall $dto): void
    //    {
    //
    //        $mvgApi = resolve($dto->apiFqcn);
    //        $result = $mvgApi->{$dto->method}(...$dto->arguments);
    //    }

    private function receiveStasisEvent($eventDto): void
    {
        $this->logger->debug("receiveStasisEvent " . var_export($eventDto, true) . "\n");

        if ($eventDto instanceof StasisStart) {
            $user = $this->getUserForPhonenumber($eventDto->channel->caller->number);
            if (!config('app.authenticate') || $user) {
                $this->prepareForCall($eventDto->channel->id, $eventDto->channel->caller, $user);
            } else {
                // todo: play rejection message
                $this->logger->debug("Hung up on " . $eventDto->channel->id);
                $this->ari->channels()->hangup($eventDto->channel->id);
                return;
            }
        }

        if ($eventDto instanceof StasisEnd) {
            $this->logger->debug("Removed State Machine for Channel (StasisEnd)" . $eventDto->channel->id);
            $this->removeStateMachine($eventDto->channel->id);
        }


        if ($eventDto instanceof ChannelHangupRequest) {
            $this->logger->debug("Removed State Machine for Channel (ChannelHangupRequest)" . $eventDto->channel->id);
            $this->removeStateMachine($eventDto->channel->id);
        }

        // Initial State

        $stateMachines = $this->getStateMachineForDto($eventDto);

        if (!count($stateMachines)) {
            $this->logger->error("AppController::myEvents:: No State Machine for Event, swallowing... ::onEvent(" . json_encode($eventDto) . ")");
        }

        foreach ($stateMachines as $stateMachine) {
            $state = $stateMachine->getCurrentState();
            $this->logger->debug("AppController::myEvents::" . $state->getName() . "::onEvent(" . json_encode($eventDto) . ")");

            $state->onEvent($eventDto);
        }
    }

    /**
     * @param AppFreeDto $dto
     * @return array<StateMachineInterface>
     */
    public function getStateMachineForDto(AppFreeDto $dto): array
    {
        if (!$dto->getChannel() || !isset($this->stateMachines[$dto->getChannel()->id])) {
            // this should probably be handled in a better way
            $this->logger->notice(sprintf("Could not determine which state machine to pass event to, so passing to all %s, event:" . var_export($dto, true), count($this->stateMachines)));
            return $this->stateMachines;
        }

        $id = $dto->getChannel()->id;
        $this->logger->debug("Selected State Machine for Channel " . $id);
        return [$this->stateMachines[$id]];
    }

    /**
     * @return mixed
     */
    public function setupAsteriskEvents()
    {
        return $this->emitter->on(Constants\EventEmitterMessageTypes::EVENT_NAME_APPFREE_MESSAGE, function (
            AppFreeDto $dto
        ) {
            $this->receiveStasisEvent($dto);
        });
    }

    /**
     * @return null
     */
    public function setupWatchdog(): void
    {
        $this->watchdogController->attachPongListener();
        $this->watchdogController->attachToEventLoop();
    }
}
