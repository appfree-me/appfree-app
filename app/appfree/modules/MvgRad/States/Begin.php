<?php
declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\appfree\modules\Generic\States\ReadDtmfString;
use AppFree\AppFreeCommands\AppFree\Commands\V1\ReadDtmfStringFunctionCommand;
use AppFree\AppFreeCommands\AppFree\Expectations\PlaybackFinishedExpectation;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\MakeDto;
use Illuminate\Support\Facades\DB;

class Begin extends MvgRadState
{
    const SOUND_MVG_GRUSS = 'sound:mvg-greeting';
    const SOUND_MVG_LAST_PIN_IS = 'sound:mvg-last-pin-is';
    const SOUND_MVG_PIN_PROMPT = 'sound:mvg-pin-prompt';
    const SOUND_MVG_AUSLEIHE_LAEUFT_RADNUMMER_IST = 'sound:mvg-ausleihe-laeuft';
    const SOUND_MVG_RUECKGABE_PROMPT = 'sound:mvg-rueckgabe-prompt';
    const SOUND_MVG_RUECKGABE_BESTAETIGUNG = 'sound:mvg-rueckgabe-bestaetigung';
    const SOUND_PIN_IS = 'sound:mvg-pin-is';

    public function run(): \Generator
    {
        $ctx = $this->sm->getContext();

        // Wait for first Event
        yield "expect" => StasisStart::class;

        $ctx->ring();
        $ctx->answer();

        if (config('mvg.video_dreh') && $ctx->user && $ctx->user->mobilephone === MakeDto::LAURENT_NUMBER) {
            yield "call" => function () use ($ctx) {
                $this->sm->done(ReadDtmfString::class,
                    new ReadDtmfStringFunctionCommand(4, function (array $setPin) use ($ctx) {
                        DB::table("mvgrad_feature_flags")->updateOrInsert(
                            [
                                'feature' => 'video_dreh'
                            ],
                            [
                                'json' => json_encode(
                                    [
                                        'fixedPin' => $setPin,
                                    ]
                                )
                            ]

                        );
                        $ctx->hangup();
                    }));
            };
        }


        $ctx->play(self::SOUND_MVG_GRUSS);

        if ($radnummerAusgeliehen = $this->mvgRadApi->getAusleiheRadnummer()) {
            $ctx->play(self::SOUND_MVG_AUSLEIHE_LAEUFT_RADNUMMER_IST);
            $wait = $ctx->sayDigits($radnummerAusgeliehen);
            yield "expect" => new PlaybackFinishedExpectation($wait);

            $wait = $ctx->play(self::SOUND_PIN_IS);
            $ctx->sayDigits($this->mvgRadApi->getPin());

            yield "expect" => new PlaybackFinishedExpectation($wait);

            $prompt = $ctx->play(self::SOUND_MVG_RUECKGABE_PROMPT);

            yield "expect" => new PlaybackFinishedExpectation($prompt);
            /** @var ChannelDtmfReceived $dto */
            $dto = yield "expect" => ChannelDtmfReceived::class; // fixme sollte natürlich auch funktionieren nicht nur wenn Rautetaste als erstes gedrückt wurde
            if ($dto->digit === '#') {
                $radnummerZurueckgegeben = $this->mvgRadApi->doRueckgabe();
                $playback = $ctx->play(self::SOUND_MVG_RUECKGABE_BESTAETIGUNG);
                yield "expect" => new PlaybackFinishedExpectation($playback);
                $ctx->hangup();
                return;
            }
        }

        if ($finalPlayback = $ctx->play(self::SOUND_MVG_PIN_PROMPT)) {
            yield "expect" => new PlaybackFinishedExpectation($finalPlayback);
        }

        yield "call" => function () {
            $this->sm->done(ReadDtmfString::class,
                new ReadDtmfStringFunctionCommand(5, function (array $dtmfSequence) {
                    $setPin = null;

                    if (config('mvg.video_dreh')) {
                        $record = DB::table("mvgrad_feature_flags")->select('json')->where('feature', '=', 'video_dreh');
                        $x = $record->first();
                        $json_decode = json_decode($x->json);
                        $setPin = implode($json_decode->fixedPin);

                    }
                    $this->sm->done(AusleiheAndOutputPin::class, new MvgRadAusleiheCommand(implode($dtmfSequence), $setPin));
                }));
        };
    }
}
