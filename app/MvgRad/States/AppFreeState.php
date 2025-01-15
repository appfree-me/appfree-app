<?php

declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\MvgRad\Interfaces\AppFreeStateInterface;
use Finite\State\State;
use Monolog\Logger;

abstract class AppFreeState extends State implements AppFreeStateInterface
{
    public const KEY_EXPECT = "expect";
    public const KEY_CALL = "call";
    public const VALID_KEYS = [self::KEY_CALL, self::KEY_EXPECT];
    protected ?\Generator $generator = null;

    public function onEvent(AppFreeDto $dto): void
    {
        $logger = resolve(Logger::class);
        $skip = false;
        $sent = false;

        if (!$this->generator) {
            $this->generator = $this->run();
        }

        if (!$this->generator->valid()) {
            throw new \Exception("State generator is exhausted but has not transitioned away from state");
        }

        if (!$this->isValidGeneratorKey($this->generator->key())) {
            throw new \Exception(sprintf("State generator returned invalid key: %s\nAllowed keys: %s", $this->generator->key(), implode(", ", self::VALID_KEYS)));
        }

        if ($this->generator->key() === self::KEY_EXPECT) {
            if ($this->generator->current() === $dto::class) {
                $this->generator->send($dto);
                $sent = true;
            } else {
                $skip = true;
            }
        }

        if ($this->generator->key() === self::KEY_CALL) {
            $fn = $this->generator->current();
            $fn();
        }

        if (!$skip && !$sent) {
            $this->generator->send($dto);
        }
    }

    /**
     *
     * Generator keys are used as commands if they are a string,
     * only allow normal array keys and known commands.
     *
     * @param mixed $key
     * @return bool
     */
    private function isValidGeneratorKey(mixed $key): bool
    {
        return is_int($key) || in_array($key, self::VALID_KEYS, true);
    }
}
