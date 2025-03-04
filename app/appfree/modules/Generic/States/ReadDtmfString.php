<?php

declare (strict_types=1);

namespace AppFree\appfree\modules\Generic\States;

use AppFree\appfree\modules\MvgRad\States\AppFreeState;
use AppFree\appfree\modules\MvgRad\States\AusleiheAndOutputPin;
use AppFree\appfree\modules\MvgRad\States\MvgRadState;
use AppFree\AppFreeCommands\AppFree\Commands\V1\ReadDtmfStringFunctionCommand;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use Finite\Event\TransitionEvent;

class ReadDtmfString extends GenericState
{
    private array $dtmfSequence = [];

    public function run(): \Generator
    {
        /** @var ReadDtmfStringFunctionCommand $command */
        // Hier würde es z. b. die Fehlersuche unterstützen, wenn man expect sagen
        // könnte, dass *das nächste* Objekt vom angegeb. Typ sein muss und nicht
        // *irgendein* nächstes
        $command = yield "expect" => ReadDtmfStringFunctionCommand::class;

        for ($i = 0; $i < $command->dtmfLength; $i++) {
            /** @var ChannelDtmfReceived $dto */
            $dto = yield "expect" => ChannelDtmfReceived::class;
            $this->dtmfSequence[] = $dto->digit;
        }

        yield "call" => function () use ($command) {
            $callback = $command->callback;
            $callback($this->dtmfSequence);
        };

    }
}
