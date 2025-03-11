<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\appfree\modules\MvgRad\Interfaces\AppFreeStateInterface;
use AppFree\AppFreeCommands\AppFree\Expectations\Expectation;
use AppFree\AppFreeCommands\AppFreeDto;
use Exception;
use Finite\State\State;
use Monolog\Logger;

abstract class AppFreeState extends State implements AppFreeStateInterface
{
    public const KEY_EXPECT = "expect";
    public const KEY_CALL = "call";
    public const VALID_KEYS = [self::KEY_CALL, self::KEY_EXPECT];
    protected ?\Generator $generator = null;
    /**
     * @var true
     */
    private bool $disable = false;

    public function onEvent(AppFreeDto $dto): void
    {
        $logger = resolve(Logger::class);
        $skip = false;
        $sent = false;

        if ($this->disable) {
            $logger->notice("Skipped DTO, Generator disabled: " . serialize($dto));
            return;
        }

        // First execution, generator is not yet existing
        if (!$this->generator) {
            $this->generator = $this->run();
        }

        // Only final states may implicitly skip events
        if (!$this->generator->valid()) {
            if ($this->isFinal()) {
                // Ignore rest of events for this state machine.
                // Could be a problem if a new call comes in and the new state machine is not yet initialized
                // todo: instead of silently dropping the event, actively refuse it (AppController can re-queue it)
                return;
            } else {
                throw new Exception("State generator is exhausted but has not transitioned away from state");
            }
        }


        if (!$this->isValidGeneratorKey($this->generator->key())) {
            throw new Exception(sprintf("State generator returned invalid key %s. Allowed keys: %s", $this->generator->key(), implode(", ", self::VALID_KEYS)));
        }

        // Allow Expectation class match or direct class match
        if ($this->generator->key() === self::KEY_EXPECT) {
            list($sent, $skip) = $this->handleExpect($dto);
        }

        if ($this->generator->key() === self::KEY_CALL) {
            $this->handleCall();
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

    /**
     * @param AppFreeDto $dto
     * @param true $sent
     * @param true $skip
     * @return true[]
     */
    public function handleExpect(AppFreeDto $dto): array
    {
        $sent = false;
        $skip = false;

        $current = $this->generator->current();
        if ($current instanceof Expectation) {
            if ($current->hasMatch($dto)) {
                $this->generator->send($dto);
                $sent = true;
            } else {
                $skip = true;
            }
            //                throw new \Exception("State yielded expect, expected value of type " . Expectation::class . ", got " . gettype($current) === "object" ? get_class($current) : serialize($current));
        } elseif ($this->generator->current() === $dto::class) {
            $this->generator->send($dto);
            $sent = true;
        } else {
            $skip = true;
        }
        return [$sent, $skip];
    }

    /**
     * @return void
     */
    public function handleCall(): void
    {
        $fn = $this->generator->current();
        try {
            $fn();
        } catch (Exception $e) {
            $this->disable = true;
            throw $e;
        }
    }
}
