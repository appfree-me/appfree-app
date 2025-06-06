<?php

declare(strict_types=1);

namespace AppFree\AppFreeCommands\Watchdog\V1;

use AppFree\AppFreeCommands\Watchdog\V1\PingPongDto as Dto;
use Random\RandomException;

/**
 * Suppress all warnings from these two rules.
 *
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
readonly class PingPongDto
{
    public function __construct(
        public string $unique_id,
        public ?float $nanoseconds_created_at = null,
        public ?float $seconds_received_at = null,
    ) {
    }

    public static function fromArray(array $array): Dto
    {
        return new self(...$array);
    }

    /**
     * @throws RandomException
     */
    public static function make(): Dto
    {
        return new self(self::generatePingId(), self::generateTimestamp());
    }

    public static function generateTimestamp(): int
    {
        return hrtime(true);
    }

    /**
     * Result needs to be unique because it is used as a database key
     *
     * @return string
     * @throws RandomException
     */
    public static function generatePingId(): string
    {
        return random_bytes(16);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
