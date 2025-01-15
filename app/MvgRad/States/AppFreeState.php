<?php

declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\MvgRad\Interfaces\AppFreeStateInterface;
use Finite\State\State;
use Monolog\Logger;

abstract class AppFreeState extends State implements AppFreeStateInterface
{
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

        if ($this->generator->key() === "expect") {
            if ($this->generator->current() === $dto::class) {
                $this->generator->send($dto);
                $sent = true;
            } else {
                $skip = true;
            }
        }

        if ($this->generator->key() === "call") {
            $fn = $this->generator->current();
            $fn();
        }

        if (!$skip &&!$sent) {
            $this->generator->send($dto);
        }
    }
}
