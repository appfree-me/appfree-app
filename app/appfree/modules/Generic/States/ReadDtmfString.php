<?php

declare (strict_types=1);

namespace AppFree\appfree\modules\Generic\States;

use AppFree\appfree\modules\MvgRad\States\AppFreeState;
use AppFree\appfree\modules\MvgRad\States\AusleiheAndOutputPin;
use AppFree\appfree\modules\MvgRad\States\MvgRadState;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use Finite\Event\TransitionEvent;

class ReadDtmfString extends GenericState
{
    private array $dtmfSequence = [];


    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
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
