<?php

declare(strict_types=1);

namespace AppFree;


use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelHangupRequest;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\Ari\Interfaces\EventReceiverInterface;
use AppFree\Ari\PhpAri;
use Finite\Exception\ObjectException;
use Finite\Exception\TransitionException;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use GuzzleHttp\Client;
use Monolog\Logger;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Swagger\Client\ApiException;
use Swagger\Client\Model\ModelInterface;

class AppController implements StatefulInterface, EventReceiverInterface
{
    public PromiseInterface $stasisClient;
    public LoopInterface $stasisLoop;
    public Logger $logger;
    protected Client $client;
    private array $stasisChannelIDs = [];
    public StateMachineInterface $sm;
    private ?string $state = null;
    public function __construct(StateMachineInterface $sm, PhpAri $phpAri, Logger $stasisLogger, Client $client)
    {
        $this->sm = $sm;
        $this->sm->ari = $phpAri;
        $this->logger = $this->sm->ari->logger;
        $this->client = $client;

    }

    /**
     * @throws ApiException
     */
    private function denyChannel(string $channelId): void
    {
        $this->logger->error("Channel $channelId denied");
        $this->sm->ari->channels()->play($channelId, ['media:please-try-call-later'], null, null, null, "channel-denied");
        sleep(2);
        $this->sm->ari->channels()->continueInDialplan($channelId);
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
        $this->sm->initialize();

        $this->stasisClient = $this->sm->ari->stasisClient;
        $this->stasisLoop = $this->sm->ari->stasisLoop;
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
    public function receive(AppFreeDto|ModelInterface $eventDto): void
    {
        $this->logger->notice("receive " . serialize($eventDto));
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
        /** @var \AppFree\MvgRad\States\MvgRadStateInterface $state */
        $state = $this->sm->getCurrentState();
        $this->logger->debug("myEvents State " . $state->getName() . "::onEvent(" . json_encode($eventDto) . ")");

        $state->onEvent($eventDto);
    }
}
