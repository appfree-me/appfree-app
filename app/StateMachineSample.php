<?php

declare(strict_types=1);

namespace AppFree;

use AppFree\MvgRad\States\Begin;
use AppFree\MvgRad\States\MvgRadStateInterface;
use AppFree\MvgRad\States\OutputPin;
use AppFree\MvgRad\States\ReadBikeNumber;
use Evenement\EventEmitter;
use Finite\State\StateInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;
use Finite\StateMachine\StateMachineInterface;
use phpari3\PhpAri;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\DataInterface;
use React\EventLoop\LoopInterface;
use stdClass;
use Zend\Log\Logger;

// $document = retrieve your stateful object

class StateMachineSample implements StatefulInterface
{
    public $stasisClient;
    public LoopInterface $stasisLoop;
    public Logger $stasisLogger;
//    protected Browser $ariEndpoint;
    protected $ariEndpoint;
    public PhpAri $phpariObject;
    private array $stasisChannelIDs = [];
    private EventEmitter $stasisEvents;
    private MvgRadStateInterface $registeredState;
    private StateMachineInterface $sm;
    private string $state;
    public MvgRadModule $mvgRadApi;

    public function __construct(string $appname)
    {
        $this->phpariObject = new PhpAri($appname);

        $this->ariEndpoint = $this->phpariObject->ariEndpoint;
        $this->stasisClient = $this->phpariObject->stasisClient;
        $this->stasisLoop = $this->phpariObject->stasisLoop;
        $this->stasisLogger = $this->phpariObject->stasisLogger;

        $this->mvgRadApi = new MvgRadModule($this);
    }

    private function denyChannel(string $channelId): void
    {
        $this->stasisLogger->err("Channel $channelId denied");
        $this->phpariObject->channels()->channel_playback($channelId, 'media:please-try-call-later', null, null, null, "channel-denied");
        sleep(2);
        $this->phpariObject->channels()->channel_continue($channelId);
    }

    private function addChannel(string $channelId): void
    {
        if (count($this->stasisChannelIDs)) {
            $this->denyChannel($channelId);
            return;
        }
        $this->stasisChannelIDs[] = $channelId;
    }

    public function getChannelID(): string
    {
        return $this->stasisChannelIDs[0];
    }

    private function removeChannel($id)
    {
        $this->stasisChannelIDs = array_diff($this->stasisChannelIDs, [$id]);
    }

    public function StasisAppEventHandler(): void
    {
        // sollte nicht mehr gebraucht werden, da alles subtypen von message
//
//        $this->stasisEvents->on('StasisStart', function ($event) {
//            $this->addChannel($event->channel->id);
//        });
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
            $conn->on("request", function (DataInterface $message) {
                $this->stasisLogger->notice("Request received!");
            });
        });
        $this->stasisClient->then(function ($conn) {
            $conn->on("handshake", function (DataInterface $message) {
                $this->stasisLogger->notice("Handshake received!");
            });
        });
        $this->stasisClient->then(function ($conn) {
            $conn->on("message", function (DataInterface $message) {
                $eventData = json_decode($message->getPayload());
                $this->stasisLogger->notice('Received message: ' . $eventData->type . ", data: " . $message->getPayload());
                $this->myEvents($eventData->type, $eventData); // todo nur relevante messages als event weiterleiten?
            });
        });
    }

    public function init(): void
    {
        $this->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $this->StasisAppEventHandler();

        $this->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $this->StasisAppConnectionHandlers();
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

    public function start()
    {
        $this->sm = new StateMachine();

//$states = [
//    ["transitions"] =>
//
//
//];

    //s1.to(s2).to(s3).to(s4)

        $mvgRadApi = new MvgRadApi();
//        $mvgRadStasisController = new MvgRadStasisAppController("appfree");

        $begin = new Begin(Begin::class, $mvgRadApi, $this, StateInterface::TYPE_INITIAL);
        $ReadBikeNumber = new ReadBikeNumber(ReadBikeNumber::class, $mvgRadApi, $this);
        $outputPin = new OutputPin(OutputPin::class, $mvgRadApi, $this, StateInterface::TYPE_FINAL);

        $this->sm->setObject($this);

        $this->sm->addState($begin);
        $this->sm->addState($ReadBikeNumber);
        $this->sm->addState($outputPin);

        $this->sm->addTransition("BeginRead", Begin::class, $ReadBikeNumber::class);
        $this->sm->addTransition("ReadOutput", $ReadBikeNumber::class, $outputPin::class);

        $this->sm->initialize();

//        $def = [
//            "states" => [
//                Begin::class =>
//            ]
//        ];

//
//            $sm = new StateMachine();
//
//// Define states
//            $sm->addState(new State('s1', StateInterface::TYPE_INITIAL));
//            $sm->addState('s2');
//            $sm->addState('s3');
//            $sm->addState(new State('s4', StateInterface::TYPE_FINAL));
//
//// Define transitions
//            $sm->addTransition('t12', 's1', 's2');
//            $sm->addTransition('t23', 's2', 's3');
//            $sm->addTransition('t34', 's3', 's4');
//            $sm->addTransition('t42', 's4', 's2');
//
//// Initialize
//            $sm->setObject(new SampleObject());
//            $sm->initialize();
//
//// Retrieve current state
//            var_dump($sm->getCurrentState());
//            $sm->apply("t12");
//            var_dump($sm->getCurrentState());
//
////var_dump($sm->getGraph());
////var_dump($sm->getObject());
//
//// Can we process a transition ?
////var_dump($sm->can('t34'));
//
    }

    private function myEvents($type, stdClass $eventData)
    {
        // Initial State
        if ($eventData->type === "StasisStart") {
            $this->stasisLogger->notice("Begin() BeginState");

            // this belongs into the framework, setup part
            $this->addChannel($eventData->channel->id);


            $this->sm->getState(Begin::class)->begin();
        }

        $this->stasisLogger->notice("onEvent " . json_encode($eventData));
        $this->sm->getCurrentState()->onEvent($eventData);
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
}
