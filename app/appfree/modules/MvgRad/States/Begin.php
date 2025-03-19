<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\appfree\modules\Generic\States\ReadDtmfString;
use AppFree\AppFreeCommands\AppFree\Commands\StateMachine\V1\ReadDtmfStringFunctionCommand;
use AppFree\AppFreeCommands\AppFree\Expectations\PlaybackFinishedExpectation;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\MakeDto;
use Generator;
use Illuminate\Support\Facades\DB;

class Begin extends MvgRadState
{
    public const SOUND_MVG_GRUSS = 'sound:mvg-greeting';
    public const SOUND_MVG_LAST_PIN_IS = 'sound:mvg-last-pin-is';
    public const SOUND_MVG_PIN_PROMPT = 'sound:mvg-pin-prompt';
    public const SOUND_MVG_AUSLEIHE_LAEUFT_RADNUMMER_IST = 'sound:mvg-ausleihe-laeuft';
    public const SOUND_MVG_MOCK_RUECKGABE_PROMPT = 'sound:mvg-rueckgabe-prompt';
    public const SOUND_MVG_MOCK_RUECKGABE_BESTAETIGUNG = 'sound:mvg-rueckgabe-bestaetigung';
    public const SOUND_PIN_IS = 'sound:mvg-pin-is';
    public const SOUND_MVG_PROD_RUECKGABE_ALERT = 'sound:mvg-rad-rueckgabe-impossible-alert' ;

    public function run(): Generator
    {
        $ctx = $this->sm->getContext();

        // Wait for first Event
        yield "expect" => StasisStart::class;

        $ctx->ring();
        $ctx->answer();

        if (config('mvg.video_dreh') && $ctx->user && $ctx->user->mobilephone === MakeDto::LAURENT_NUMBER) {
            yield "call" => function () use ($ctx) {
                $this->sm->done(
                    ReadDtmfString::class,
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
                    })
                );
            };
        }


        $ctx->play(self::SOUND_MVG_GRUSS);

        $radnummerAusgeliehen = $this->mvgRadApi->getAusleiheRadnummer();
        if ($radnummerAusgeliehen) {
            $ctx->play(self::SOUND_MVG_AUSLEIHE_LAEUFT_RADNUMMER_IST);
            $wait = $ctx->sayDigits($radnummerAusgeliehen);
            yield "expect" => new PlaybackFinishedExpectation($wait);

            $wait = $ctx->play(self::SOUND_PIN_IS);
            $ctx->sayDigits($this->mvgRadApi->getPin());

            yield "expect" => new PlaybackFinishedExpectation($wait);

            // Mock API has a bike return feature for demonstration purposes
            if ($this->mvgRadApi->isMock()) {
                $prompt = $ctx->play(self::SOUND_MVG_MOCK_RUECKGABE_PROMPT);


                yield "expect" => new PlaybackFinishedExpectation($prompt);
                /** @var ChannelDtmfReceived $dto */
                $dto = yield "expect" => ChannelDtmfReceived::class; // fixme sollte natürlich auch funktionieren nicht nur wenn Rautetaste als erstes gedrückt wurde
                if ($dto->digit === '#') {
                    $this->mvgRadApi->doRueckgabe();
                    $playback = $ctx->play(self::SOUND_MVG_MOCK_RUECKGABE_BESTAETIGUNG);
                    yield "expect" => new PlaybackFinishedExpectation($playback);
                    $ctx->hangup();
                    return;
                }
            } // Prod API just tells the user to return it on the bike and hangs up on him :-(
            else {
                $prompt = $ctx->play(self::SOUND_MVG_PROD_RUECKGABE_ALERT);
                yield "expect" => new PlaybackFinishedExpectation($prompt);

                $ctx->hangup();
                return;
            }
        }

        $finalPlayback = $ctx->play(self::SOUND_MVG_PIN_PROMPT);
        if ($finalPlayback) {
            yield "expect" => new PlaybackFinishedExpectation($finalPlayback);
        }

        yield "call" => function () {
            $this->sm->done(
                ReadDtmfString::class,
                new ReadDtmfStringFunctionCommand(5, function (array $dtmfSequence) {
                    $setPin = null;

                    if (config('mvg.video_dreh')) {
                        $record = DB::table("mvgrad_feature_flags")->select('json')->where('feature', '=', 'video_dreh');
                        $x = $record->first();
                        $decoded = json_decode($x->json);
                        $setPin = implode($decoded->fixedPin);
                    }
                    $this->sm->done(AusleiheAndOutputPin::class, new MvgRadAusleiheCommand(implode($dtmfSequence), $setPin));
                })
            );
        };
    }
}
