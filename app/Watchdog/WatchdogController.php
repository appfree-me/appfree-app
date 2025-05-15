<?php

declare(strict_types=1);

namespace AppFree\Watchdog;

use App\ReactWebsocketInterface;
use AppFree\AppFreeCommands\Watchdog\V1\PingPongDto as Dto;
use AppFree\ErrorHandling\Constants\Errors;
use AppFree\Models\WatchdogLog;
use Exception;
use Monolog\Logger;
use Random\RandomException;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\FrameInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

readonly class WatchdogController
{
    public function __construct(
        private Logger           $logger,
        private PromiseInterface|ReactWebsocketInterface $wsClient, //fixme typehint
        private ?LoopInterface    $eventLoop,
        public int               $intervalSeconds
    ) {}

    /**
     * @throws RandomException
     */
    public function sendPing(): void
    {
        $dto = Dto::make();
        $this->saveToLog($dto);

        $this->wsClient->then(
            function (WebSocket $conn) use ($dto) {
                $conn->send(new Frame(
                    $dto->unique_id,
                    true,
                    Frame::OP_PING
                ));
            }
        );
    }

    public function receivePong(FrameInterface $frame): void
    {
        try {
            $dto = Dto::fromArray([
                "unique_id" => $frame->getPayload(),
                "seconds_received_at" => Dto::generateTimestamp() + 5 * pow(10, 9)
            ]);
            $this->saveToLog($dto);
        } catch (Exception $exception) {
            $this->logger->error(Errors::E_WATCHDOG_COULD_MAKE_DTO, ["exception" => $exception, "ERROR_ID" => Errors::E_WATCHDOG_COULD_MAKE_DTO]);
        }
    }

    private function saveToLog(Dto $dto): void
    {
        $entity = WatchdogLog::firstWhere('unique_id', $dto->unique_id) ?? new WatchdogLog();

        try {
            $entity->augment($dto);
            $entity->save();
        } catch (Exception $e) {
            $this->logger->error(Errors::E_WATCHDOG_COULD_NOT_SAVE, ["dto" => $dto->toArray(), "exception" => $e, "ERROR_ID" => Errors::E_WATCHDOG_COULD_NOT_SAVE]);
        }
    }

    public function attachToEventLoop(): void
    {
        $this->eventLoop->addPeriodicTimer($this->intervalSeconds, function () {
            $this->sendPing();
        });
    }

    public function attachPongListener(): void
    {
        $this->wsClient->then(
            function (WebSocket $conn) {
                $conn->on('pong', function (FrameInterface $frame) {
                    $this->receivePong($frame);
                });
            }
        );
    }
}
