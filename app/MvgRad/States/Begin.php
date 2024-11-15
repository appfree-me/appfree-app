<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\MvgRadModule;
use stdClass;

class Begin extends MvgRadState implements MvgRadStateInterface
{
    public function vorbedingung(): bool
    {
        // keine vorbedingung

        return true;
    }

    public function begin(): mixed
    {
        $controller = $this->stateMachineSample;
        $this->stateMachineSample->phpariObject->channels()->ring($controller->getChannelID());
        sleep(1);
        $controller->phpariObject->channels()->answer($controller->getChannelID());
        $controller->stasisLogger->notice("channel_playback() play1 " . $controller->getChannelID());
        $controller->phpariObject->channels()->play($controller->getChannelID(), ['sound:mvg-greeting'], null, null, null, "play2");

        if ($controller->mvgRadApi->hasLastPin()) {
            $controller->phpariObject->channels()->play($controller->getChannelID(), ['sound:mvg-last-pin-is'], null, null, null, "play3");
            MvgRadModule::sayDigits("123", $controller);
        }
        $controller->phpariObject->channels()->play($controller->getChannelID(), ['sound:mvg-pin-prompt'], null, null, null, "play4");

        return $this->stateMachineSample->done(Begin::class);
    }

    public function onEvent(stdClass $eventData): mixed
    {
        return null;
    }
}
