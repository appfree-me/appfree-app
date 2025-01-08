<?php

declare(strict_types=1);

namespace AppFree;


use AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFreeCommands\Stasis\Events\V1\StasisStart;
use Finite\Exception\ObjectException;
use Finite\Exception\TransitionException;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use Monolog\Logger;
use MvgRad\Loader;
use PhpAri3\Interfaces\EventReceiverInterface;
use phpari3\PhpAri;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\DataInterface;
use React\EventLoop\LoopInterface;
use stdClass;
use Swagger\Client\ApiException;

class AppController implements StatefulInterface, EventReceiverInterface
{
    public $stasisClient;
    public LoopInterface $stasisLoop;
    public Logger $stasisLogger;
    protected $ariEndpoint;
    private array $stasisChannelIDs = [];
    public StateMachineInterface $sm;
    private ?string $state = null;
    private string $appName;

    public function __construct(string $appName)
    {
        $this->appName = $appName;
    }

    /**
     * @throws ApiException
     */
    private function denyChannel(string $channelId): void
    {
        $this->stasisLogger->error("Channel $channelId denied");
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
        $this->stasisLogger->notice("Added Channel", [$channelId]);
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
    }

    public function StasisAppConnectionHandlers(): void
    {
        $this->stasisClient->then(function ($conn) {
            $conn->on("message", function (DataInterface $message) {
                $eventData = json_decode($message->getPayload());
                $this->stasisLogger->notice(__FILE__ . 'Received message: ' . $eventData->type . ", data: " . $message->getPayload());
                $eventDto = MakeDto::make($eventData);

                $this->myEvents($eventDto); // todo nur relevante messages als event weiterleiten?
            });
        });
    }

    private function myEvents($eventDto): void
    {
//        $dto = MakeDto::make($eventData);

        // Initial State
        /** @var \MvgRad\States\MvgRadStateInterface $state */
        $state = $this->sm->getCurrentState();
        $this->stasisLogger->debug("State " . $state->getName() . "::onEvent(" . json_encode($eventDto) . ")");

        $state->onEvent($eventDto);
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
        //  $this->sm->phpariObject->init();
        $this->stasisLogger = $this->sm->phpariObject->logger;

        $this->ariEndpoint = $this->sm->phpariObject->ariEndpoint;
        $this->stasisClient = $this->sm->phpariObject->stasisClient;
        $this->stasisLoop = $this->sm->phpariObject->stasisLoop;

        $this->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $this->StasisAppEventHandler();

        $this->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $this->StasisAppConnectionHandlers();
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
     *
     * Called from phpari
     *
     * @throws TransitionException
     * @throws ApiException
     */
    public function receive(string $eventName, stdClass $eventData): void
    {
        $eventDto = MakeDto::make($eventData);

        if ($eventDto instanceof StasisStart) {
            $this->addChannel($eventDto->channel->id);
            return;
        }

        if ($eventDto instanceof StasisEnd) {
            $this->removeChannel($eventDto->channel->id);
            return;
        }

        $this->stasisLogger->notice("onEvent " . json_encode($eventData));
        $this->myEvents($eventDto);
    }
}
