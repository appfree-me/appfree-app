<?php

declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppController;
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

    public function onEvent(AppController $appController, AppFreeDto $dto): void
    {
        $logger = resolve(Logger::class);

        // fixme: onEvent soll nicht für jeden Event neu aufgerufen werden, sondern immer nur einmal pro State enter. (onEnter?)
        // Events während man im State ist vll bei onEvent geben?
        // Aber besser mit unterbrochener Coroutine


        if (!$this->generator) {
            $this->generator = $this->run($appController);
        }

        if ($this->generator->valid()) {
            if (!$this->ret) {
                $logger->debug("Sending DTO to generator: " . $dto::class);
                $this->ret = $this->generator->send($dto);
            } else if ($this->generator->key() === "expected" && $this->ret === $dto::class) {
                $logger->debug("Sending DTO to generator: " . $dto::class);
                $this->ret = $this->generator->send($dto);
            } else if ($this->generator->key() !== "expected") {
                $logger->debug("Sending DTO to generator: " . $dto::class);
                $this->ret = $this->generator->send($dto);
                $skipped = true;
            } else {
                $logger->debug("Skipped DTO => " . $dto::class . " (expected: $this->ret )");
            }

            if (is_callable($this->ret)) {
                $logger->debug("Calling callable from run function");
                $x = $this->ret;
                $x();
                $this->ret = null;
            }
            //else if ($this->generator->key() == "expected") {
        } else {
            throw new \Exception("State generator is not valid but has not transitioned away from state");
//            resolve(Logger::class)->error("State generator is not valid but has not transitioned away from state");
        }
    }
}
