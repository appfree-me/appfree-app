<?php
declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\appfree\modules\Generic\States\ReadDtmfString;
use AppFree\AppFreeCommands\AppFree\Commands\V1\ReadDtmfStringFunctionCommand;
use AppFree\AppFreeCommands\AppFree\Expectations\PlaybackFinishedExpectation;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;

class Begin extends MvgRadState
{
    const SOUND_MVG_GREETING = 'sound:mvg-greeting';
    const SOUND_MVG_LAST_PIN_IS = 'sound:mvg-last-pin-is';
    const SOUND_MVG_PIN_PROMPT = 'sound:mvg-pin-prompt';

    public function run(): \Generator
    {
        $ctx = $this->sm->getContext();

        // Wait for first Event
        yield "expect" => StasisStart::class;

        $ctx->ring();
        $ctx->answer();
        $ctx->play(self::SOUND_MVG_GREETING);

        if ($this->mvgRadModule->hasLastPin()) {
            $ctx->play(self::SOUND_MVG_LAST_PIN_IS);
            $ctx->sayDigits("123");
        }
        $finalPlayback = $ctx->play(self::SOUND_MVG_PIN_PROMPT);

        yield "expect" => new PlaybackFinishedExpectation($finalPlayback);

        yield "call" => function () {
            $this->sm->done(ReadDtmfString::class,
                new ReadDtmfStringFunctionCommand(5, function (array $dtmfSequence) {
                    $this->sm->done(AusleiheAndOutputPin::class, new MvgRadAusleiheCommand(implode($dtmfSequence)));
                }));
        };
    }
}
