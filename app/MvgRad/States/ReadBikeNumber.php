<?php

declare (strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use Finite\Event\TransitionEvent;

class ReadBikeNumber extends MvgRadState
{
    private array $dtmfSequence = [];


    public function vorbedingung(): bool
    {
        // TODO: Implement vorbedingung() method.
        return true;
    }

    public function onEvent(AppFreeDto $dto): void
    {
        print(__CLASS__ . "->onEvent(" . json_encode($dto));
        if ($dto instanceof ChannelDtmfReceived) {
            $this->addDtmf($dto->digit);
        }

        if (count($this->dtmfSequence) === 5) {
            $this->sm->done(AusleiheAndOutputPin::class, new MvgRadAusleiheCommand(implode($this->dtmfSequence)));
        }

        return;
    }

    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
    }

    public function before(TransitionEvent $event): mixed
    {
        // TODO: Implement begin() method.
        return null;
    }    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement begin() method.
        return null;
    }

    public function run(): \Generator
    {
        // TODO: Implement run() method.
        yield;
    }
}
