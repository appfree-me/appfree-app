<?php
declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\appfree\modules\Generic\States\ReadDtmfString;
use AppFree\AppFreeCommands\AppFree\Commands\V1\ReadDtmfStringFunctionCommand;
use AppFree\AppFreeCommands\AppFree\Expectations\PlaybackFinishedExpectation;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;

class Begin extends MvgRadState
{
    const SOUND_MVG_GRUSS = 'sound:mvg-greeting';
    const SOUND_MVG_LAST_PIN_IS = 'sound:mvg-last-pin-is';
    const SOUND_MVG_PIN_PROMPT = 'sound:mvg-pin-prompt';
    const SOUND_MVG_AUSLEIHE_LAEUFT_PIN_IST = 'sound:mvg-ausleihe-laeuft';
    const SOUND_MVG_RUECKGABE_PROMPT = 'sound:mvg-rueckgabe-prompt';
    const SOUND_MVG_RUECKGABE_BESTAETIGUNG = 'sound:mvg-rueckgabe-bestetigung';

    public function run(): \Generator
    {
        $ctx = $this->sm->getContext();

        // Wait for first Event
        yield "expect" => StasisStart::class;

        $ctx->ring();
        $ctx->answer();
        $ctx->play(self::SOUND_MVG_GRUSS);


        if ($this->mvgRadApi->isAusleiheRunning()) {
            $ctx->play(self::SOUND_MVG_AUSLEIHE_LAEUFT_PIN_IST);
            $ctx->sayDigits($this->mvgRadApi->getPin());
            $prompt = $ctx->play(self::SOUND_MVG_RUECKGABE_PROMPT);

            yield "expect" => new PlaybackFinishedExpectation($prompt);
            /** @var ChannelDtmfReceived $dto */
            $dto = yield "expect" => ChannelDtmfReceived::class; // fixme sollte natürlich auch funktionieren nicht nur wenn Rautetaste als erstes gedrückt wurde
            if ($dto->digit === '#') {
                $radnummerZurueckgegeben = $this->mvgRadApi->doRueckgabe();
                $playback = $ctx->play(self::SOUND_MVG_RUECKGABE_BESTAETIGUNG);
                yield "expect" => new PlaybackFinishedExpectation($playback);
                $ctx->sayDigits($radnummerZurueckgegeben);
                $ctx->hangup();
            }
        }

        if ($finalPlayback = $ctx->play(self::SOUND_MVG_PIN_PROMPT)) {
            yield "expect" => new PlaybackFinishedExpectation($finalPlayback);
        }

        yield "call" => function () {
            $this->sm->done(ReadDtmfString::class,
                new ReadDtmfStringFunctionCommand(5, function (array $dtmfSequence) {
                    $this->sm->done(AusleiheAndOutputPin::class, new MvgRadAusleiheCommand(implode($dtmfSequence)));
                }));
        };
    }
}
