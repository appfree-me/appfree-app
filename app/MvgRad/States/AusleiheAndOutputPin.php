<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\MvgRad\Api\MvgRadModule;
use Finite\Event\TransitionEvent;


class AusleiheAndOutputPin extends MvgRadState
{
    public function vorbedingung(): bool
    {
        return true;
    }

    public function before(TransitionEvent $event): mixed
    {
        // TODO: Implement ausfuehren() method.
        return null;
    }

    public function onEvent(AppFreeDto $dto): void
    {
        $appController = resolve(AppController::class);
        $channelID = $appController->getChannelID();
        $channelsApi = $appController->ari->channels();
        /** @var MvgRadAusleiheCommand $dto */
        $pin = $this->mvgRadApi->doAusleihe($dto->radnummer);
        MvgRadModule::sayDigits($pin, $channelID, $channelsApi);

        $channelsApi->hangup($channelID);

        // TODO: Implement event() method.
        return ;
    }

    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement after() method.
        return null;
    }

    public function run(): \Generator
    {
        // TODO: Implement run() method.
        yield;
    }
}
