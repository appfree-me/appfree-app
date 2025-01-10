<?php

declare(strict_types=1);

namespace AppFree;


use AppFreeCommands\AppFreeDto;
use AppFreeCommands\Stasis\Events\V1\ChannelHangupRequest;
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
use React\EventLoop\LoopInterface;
use Swagger\Client\ApiException;
use Swagger\Client\Model\ModelInterface;

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
    public function start(PhpAri $phpAri, $sm): void
    {
        $this->sm = $sm;
        $this->sm->initialize();

        $this->sm->phpariObject = $phpAri;
        $this->stasisLogger = $this->sm->phpariObject->logger;

        $this->ariEndpoint = $this->sm->phpariObject->ariEndpoint;
        $this->stasisClient = $this->sm->phpariObject->stasisClient;
        $this->stasisLoop = $this->sm->phpariObject->stasisLoop;
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
    public function receive(AppFreeDto|ModelInterface $eventDto): void
    {
        $this->stasisLogger->notice("receive " . serialize($eventDto));
        $this->myEvents($eventDto);
    }

    private function myEvents($eventDto): void
    {
        if ($eventDto instanceof StasisStart) {
            $this->addChannel($eventDto->channel->id);
        }

        if ($eventDto instanceof StasisEnd) {
            $this->removeChannel($eventDto->channel->id);
        }


        if ($eventDto instanceof ChannelHangupRequest) {
            $this->removeChannel($eventDto->channel->id);
        }

        // Initial State
        /** @var \MvgRad\States\MvgRadStateInterface $state */
        $state = $this->sm->getCurrentState();
        $this->stasisLogger->debug("myEvents State " . $state->getName() . "::onEvent(" . json_encode($eventDto) . ")");

        $state->onEvent($eventDto);
    }
}
