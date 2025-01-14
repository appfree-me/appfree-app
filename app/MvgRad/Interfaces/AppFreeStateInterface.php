<?php

namespace AppFree\MvgRad\Interfaces;

use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use Finite\Event\TransitionEvent;
use Finite\State\StateInterface;

interface AppFreeStateInterface extends StateInterface
{
    public function vorbedingung(): bool;

    public function before(TransitionEvent $event): mixed;

    public function after(TransitionEvent $event): mixed;

    public function onEvent(AppFreeDto $dto): void;

    public function run(): \Generator;
}
