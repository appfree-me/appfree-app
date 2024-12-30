<?php

declare(strict_types=1);

namespace AppFree;


use Evenement\EventEmitter;
use Finite\Exception\ObjectException;
use Finite\Exception\TransitionException;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use MvgRad\Loader;
use MvgRad\States\Begin;
use PhpAri3\Interfaces\EventReceiverInterface;
use phpari3\PhpAri;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\DataInterface;
use React\EventLoop\LoopInterface;
use stdClass;
use Swagger\Client\ApiException;
use Zend\Log\Logger;

class AppController implements StatefulInterface, EventReceiverInterface
{
    public $stasisClient;
    public LoopInterface $stasisLoop;
    public Logger $stasisLogger;
    protected $ariEndpoint;
    private array $stasisChannelIDs = [];
    private EventEmitter $stasisEvents;
    private MvgRadStateInterface $registeredState;
    public StateMachineInterface $sm;
    private string $state;
    public MvgRadModule $mvgRadApi;
    private string $appName;

    public function __construct(string $appName)
    {
        $this->appName = $appName;

        $this->mvgRadApi = new MvgRadModule($this);
    }

    /**
     * @throws ApiException
     */
    private function denyChannel(string $channelId): void
    {
        $this->stasisLogger->err("Channel $channelId denied");
        $this->sm->phpariObject->channels()->play($channelId, ['media:please-try-call-later'], null, null, null, "channel-denied");
        sleep(2);
        $this->sm->phpariObject->channels()->continueInDialplan($channelId);
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
    }

    public function getChannelID(): ?string
    {
        return $this->stasisChannelIDs[0] ?? null;
    }

    private function removeChannel($id)
    {
        $this->stasisChannelIDs = array_diff($this->stasisChannelIDs, [$id]);
    }

    public function StasisAppEventHandler(): void
    {
        // New call coming on, new channel being created - register a new State Machine for this call
//        $this->stasisEvents->on('StasisStart', function ($event) {
//            $this->addChannel($event->channel->id);
//        });

        $this->stasisClient->then(function ($conn) {
            $conn->on("message", function (DataInterface $message) {
                $eventData = json_decode($message->getPayload());
                if ($eventData->type == "StasisStart") {
                    $this->addChannel($eventData->channel->id);
                }
                if ($eventData->type == "StasisEnd") {
                    $this->removeChannel($eventData->channel->id);
                }
            });
        });


//        $this->stasisEvents->on('StasisEnd', function ($event) {
//            $this->removeChannel($event->channel->id);
//        });
//        $this->stasisEvents->on('ChannelChangeState', function ($event) {
//            $this->stasisLogger->notice("+++ ChannelChangeState +++ " . json_encode($event) . "\n");
//        });
//        $this->stasisEvents->on('PlaybackStarted', function ($event) {
//            $this->stasisLogger->notice("+++ PlaybackStarted +++ " . json_encode($event->playback) . "\n");
//        });
//        $this->stasisEvents->on('PlaybackFinished', function ($event) {
////            $this->stasisLogger->notice("+++ PlaybackFinished +++ " . json_encode($event->playback->id) . "\n");
//        });
    }

    public function StasisAppConnectionHandlers(): void
    {
        $this->stasisClient->then(function ($conn) {
            $conn->on("message", function (DataInterface $message) {
                $eventData = json_decode($message->getPayload());
                $this->stasisLogger->notice('Received message: ' . $eventData->type . ", data: " . $message->getPayload());
                $this->myEvents($eventData->type, $eventData); // todo nur relevante messages als event weiterleiten?
            });
        });
    }

    private function myEvents(string $eventType, stdClass $eventData): void
    {
        // Initial State
        /** @var \MvgRad\States\MvgRadStateInterface $state */
        $state = $this->sm->getCurrentState();
        $this->stasisLogger->debug("State " . $state->getName() . "::onEvent(" . json_encode($eventData) . ")");

        $state->onEvent($eventData);
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
        $this->sm = Loader::load($this);
        $this->sm->initialize();

        $this->sm->phpariObject = new PhpAri($this->appName, $this);
        $this->ariEndpoint = $this->sm->phpariObject->ariEndpoint;
        $this->stasisClient = $this->sm->phpariObject->stasisClient;
        $this->stasisLoop = $this->sm->phpariObject->stasisLoop;
        $this->stasisLogger = $this->sm->phpariObject->logger;

        $this->sm->phpariObject->init();

        $this->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $this->StasisAppEventHandler();

        $this->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $this->StasisAppConnectionHandlers();
    }

    public function done(string $stateName): mixed
    {
        if ($this->sm->getCurrentState()->getName() === Begin::class) {
            $this->stasisLogger->notice("Apply BeginRead");
            return $this->sm->apply("BeginRead");
        } elseif ($this->sm->getCurrentState()->getName() === ReadBikeNumber::class) {
            $this->stasisLogger->notice("Apply ReadOutput");
            return $this->sm->apply("ReadOutput");
        }

        return null;
    }

    public function getFiniteState()
    {
        return $this->state;
    }

    public function setFiniteState($state)
    {
        $this->state = $state;
    }

    /**
     * @throws TransitionException
     * @throws ApiException
     */
    public function receive(string $eventName, stdClass $eventData): void
    {
        // Initial State
        if ($eventData->type === "StasisStart") {
            $this->stasisLogger->notice("Begin() BeginState");

            // this belongs into the framework, setup part
            $this->addChannel($eventData->channel->id);

//            $this->sm->getState(Begin::class)->begin();
        }

        $this->stasisLogger->notice("onEvent " . json_encode($eventData));
        $state = $this->sm->getCurrentState();
        $state->onEvent($eventData);
    }
}
