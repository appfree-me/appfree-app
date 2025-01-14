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
    protected $ret = null;
    protected bool $firstRun = true;

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

        if ($this->firstRun) {
            $this->ret = $this->generator->current();
            $this->firstRun = false;
        }

        if ($this->generator->key() === "expect") {
            if ($this->ret === $dto::class) {
                $sent = true;
                $this->ret = $this->generator->send($dto);
            } else {
                $skip = true;
            }
        }
        if ($this->generator->key() === "call") {
            $ret = $this->ret;
            $ret();
            $this->ret = null;
        }

        if (!$skip && !$sent) {
            $this->ret = $this->generator->send($dto);
        }
    }
}
