<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;


use AppFree\AppFreeCommands\AppFreeDto;
use Finite\Event\TransitionEvent;
use Swagger\Client\Model\ModelInterface;

class ReadDtmfState extends MvgRadState implements MvgRadStateInterface {

    private array $dtmfSequence = [];

    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
    }


    public function vorbedingung(): bool
    {
        // TODO: Implement vorbedingung() method.
    }

    public function before(TransitionEvent $event): mixed
    {
        // TODO: Implement before() method.
    }

    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement after() method.
    }

    public function onEvent(AppFreeDto|ModelInterface $dto): mixed
    {


        // TODO: Implement onEvent() method.
    }
}
