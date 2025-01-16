<?php
declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\AppController;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;
use AppFree\Ari\PhpAri;


class AusleiheAndOutputPin extends MvgRadState
{

    public function run(): \Generator
    {
        $dto = yield "expect" => MvgRadAusleiheCommand::class;

        $ari = resolve(PhpAri::class);

        $channelsApi = $ari->channels();
        $appController = resolve(AppController::class);
        $channelID = $appController->getChannelID(); //fixme should be specific to this state machine
        /** @var MvgRadAusleiheCommand $dto */
        $pin = $this->mvgRadApi->doAusleihe($dto->radnummer);
        MvgRadModule::sayDigits($pin, $channelID, $channelsApi);

        yield "expect" => PlaybackFinished::class;
        // MIt hangup ist hier der Punkt wo man expect argumente wie playback id mitgeben muss,
        // sonst wird zu früh aufgelegt! fixme

        $channelsApi->hangup($channelID);
    }
}
