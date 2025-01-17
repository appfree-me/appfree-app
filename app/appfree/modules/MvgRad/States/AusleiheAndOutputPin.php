<?php
declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\AppController;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\AppFreeCommands\AppFree\Expectations\PlaybackFinishedExpectation;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;
use AppFree\Ari\PhpAri;
use Swagger\Client\Api\ChannelsApi;


class AusleiheAndOutputPin extends MvgRadState
{

    public function run(): \Generator
    {
        $ctx = $this->sm->getContext();

        $dto = yield "expect" => MvgRadAusleiheCommand::class;

        $pin = $this->mvgRadApi->doAusleihe($dto->radnummer);
        $lastPlaybackId = $ctx->sayDigits($pin);

        yield "expect" => new PlaybackFinishedExpectation($lastPlaybackId);

        $ctx->hangup();
    }
}
