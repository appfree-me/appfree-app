<?php

declare(strict_types=1);

namespace AppFree\Watchdog;

use AppFree\AppFreeCommands\Watchdog\V1\PingPongDto as Dto;
use AppFree\ErrorHandling\Constants\ErrorIds;
use AppFree\Models\WatchdogLog;
use Exception;
use Monolog\Logger;
use Random\RandomException;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\FrameInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

class WatchdogController
{
    public const int INTERVAL_SECONDS = 60;

    public function __construct(
        private readonly Logger           $stasisLogger,
        private readonly PromiseInterface $wsClient,
        private readonly LoopInterface    $eventLoop
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
                "seconds_received_at" => Dto::generateTimestamp()
            ]);
            $this->saveToLog($dto);
        } catch (Exception $exception) {
            $this->stasisLogger->error(ErrorIds::E_WATCHDOG_COULD_MAKE_DTO, ["exception" => $exception, "ERROR_ID" => ErrorIds::E_WATCHDOG_COULD_MAKE_DTO]);
        }
    }

    private function saveToLog(Dto $dto): void
    {
        $entity = WatchdogLog::firstWhere('unique_id', $dto->unique_id) ?? new WatchdogLog();
        $entity->fill(array_filter($dto->toArray()));

        try {
            $entity->save();
        } catch (Exception $e) {
            $this->stasisLogger->error(ErrorIds::E_WATCHDOG_COULD_NOT_SAVE, ["exception" => $e, "ERROR_ID" => ErrorIds::E_WATCHDOG_COULD_NOT_SAVE]);
        }
    }

    public function attachToEventLoop(): void
    {
        $this->eventLoop->addPeriodicTimer(self::INTERVAL_SECONDS, function () {
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
