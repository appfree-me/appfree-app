<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\AppFreeCommands\AppFree\Expectations\PlaybackFinishedExpectation;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;

class AusleiheAndOutputPin extends MvgRadState
{
    public const SOUND_PIN_IS = 'sound:mvg-pin-is';

    public function run(): \Generator
    {
        $ctx = $this->sm->getContext();
        $dto = yield "expect" => MvgRadAusleiheCommand::class;
        $pin = $this->mvgRadApi->doAusleihe($dto->radnummer, $dto->setPin);

        for ($i = 0; $i < 10; $i++) {
            $wait = $ctx->play(self::SOUND_PIN_IS);
            yield "expect" => new PlaybackFinishedExpectation($wait);

            $lastPlaybackId = $ctx->sayDigits($pin);
            yield "expect" => new PlaybackFinishedExpectation($lastPlaybackId);
            sleep(2);
        }

        $ctx->hangup();
    }
}
