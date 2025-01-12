<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\MvgRad\Api\MvgRadModule;
use Finite\Event\TransitionEvent;
use Swagger\Client\Model\ModelInterface;


class OutputPin extends MvgRadState implements MvgRadStateInterface
{
    public function vorbedingung(): bool
    {
        // TODO: Implement vorbedingung() method.
        return true;
    }

    public function before(TransitionEvent $event): mixed
    {
        // TODO: Implement ausfuehren() method.
        return null;
    }

    public function onEvent(AppController $appController, AppFreeDto|ModelInterface $dto): void
    {
        $channelID = $appController->getChannelID();
        $channelsApi = $appController->ari->channels();
        MvgRadModule::sayDigits("7777", $channelID, $channelsApi);

        $channelsApi->hangup($channelID);

        // TODO: Implement event() method.
        return ;
    }

    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement after() method.
        return null;
    }
}
