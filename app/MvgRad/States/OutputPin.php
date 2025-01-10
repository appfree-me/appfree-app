<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;

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

    public function onEvent(AppFreeDto|ModelInterface $dto): mixed
    {
        $channelID = $this->appController->getChannelID();
        $channelsApi = $this->sm->phpariObject->channels();
        MvgRadModule::sayDigits("7777", $channelID, $channelsApi);

        $channelsApi->hangup($channelID);

        // TODO: Implement event() method.
        return null;
    }

    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement after() method.
        return null;
    }
}
