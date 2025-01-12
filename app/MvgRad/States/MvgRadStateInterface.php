<?php

namespace AppFree\MvgRad\States;

use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use Finite\Event\TransitionEvent;
use Finite\State\StateInterface;
use Swagger\Client\Model\ModelInterface;

interface MvgRadStateInterface extends StateInterface
{

    public function vorbedingung(): bool;
    public function before(TransitionEvent $event): mixed;
    public function after(TransitionEvent $event): mixed;

    public function onEvent(AppController $appController, AppFreeDto|ModelInterface $dto): void;
}
