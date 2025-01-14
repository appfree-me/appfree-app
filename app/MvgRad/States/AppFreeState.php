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
    //DSL:
    //es muss auf events gewartet werden können
// waitFor(PlaybackFinishedEvent::class)->then()->then()->...
// subzustände / ad-hoc-zustände zur wiederverwendung von funktionalität
// später auch als library
//
//

    public function onEvent(AppFreeDto $dto): void
    {
        $logger = resolve(Logger::class);
        $skip = false;
        $sent = false;

        if (!$this->generator) {
            $this->generator = $this->run();
        }
//
        if (!$this->generator->valid()) {
            throw new \Exception("State generator is not valid but has not transitioned away from state");
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
//
//
//        if ($this->generator->valid()) {
//            if (!$this->ret) {
//                $logger->debug("Sending DTO to generator: " . $dto::class);
//                $this->ret = $this->generator->send($dto);
//            } else if ($this->generator->key() === "expect" && $this->ret === $dto::class) {
//                $logger->debug("Sending DTO to generator: " . $dto::class);
//                $this->ret = $this->generator->send($dto);
//            } else if ($this->generator->key() !== "expect") {
//                $logger->debug("Sending DTO to generator: " . $dto::class);
//                $this->ret = $this->generator->send($dto);
//                $skipped = true;
//            } else {
//                $logger->debug("Skipped DTO => " . $dto::class . " (expected: $this->ret )");
//            }
//
//            if (is_callable($this->ret)) {
//                $logger->debug("Calling callable from run function");
//                $x = $this->ret;
//                $x();
//                $this->ret = null;
//            }
//            //else if ($this->generator->key() == "expected") {
//        } else {
//            throw new \Exception("State generator is not valid but has not transitioned away from state");
////            resolve(Logger::class)->error("State generator is not valid but has not transitioned away from state");
//        }
    }
}
