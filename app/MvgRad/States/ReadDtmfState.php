<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;


use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use Finite\Event\TransitionEvent;
use Swagger\Client\Model\ModelInterface;

class ReadDtmfState extends MvgRadState  {

    private array $dtmfSequence = [];

    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
    }


    public function vorbedingung(): bool
    {
        // TODO: Implement vorbedingung() method.
        return true;
    }

    public function before(TransitionEvent $event): mixed
    {
        // TODO: Implement before() method.
        return null;
    }

    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement after() method.
        return null;
    }

    public function onEvent(AppFreeDto $dto): void
    {


        // TODO: Implement onEvent() method.
    }

    public function run(): \Generator
    {
        // TODO: Implement run() method.
        yield;
    }
}
