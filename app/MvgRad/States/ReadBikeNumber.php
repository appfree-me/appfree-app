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
        for ($i=0; $i<5;$i++) {
            /** @var ChannelDtmfReceived $dto */
            $dto = yield "expect" => ChannelDtmfReceived::class;
            $this->addDtmf($dto->digit);
        }

        yield "call" => function () {
            $this->sm->done(AusleiheAndOutputPin::class, new MvgRadAusleiheCommand(implode($this->dtmfSequence)));
        };

    }
}
