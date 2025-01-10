<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppFreeCommands\AppFreeDto;
use Finite\Event\TransitionEvent;
use Swagger\Client\Model\ModelInterface;

class Begin extends MvgRadState implements MvgRadStateInterface
{
    const SOUND_MVG_GREETING = 'sound:mvg-greeting';
    const SOUND_MVG_LAST_PIN_IS = 'sound:mvg-last-pin-is';
    const SOUND_MVG_PIN_PROMPT = 'sound:mvg-pin-prompt';

    public function vorbedingung(): bool
    {
        // keine vorbedingung

        return true;
    }
    //DSL:
    //es muss auf events gewartet werden können
// waitFor(PlaybackFinishedEvent::class)->then()->then()->...
// subzustände / ad-hoc-zustände zur wiederverwendung von funktionalität
// später auch als library
//
//
    public function onEvent(AppFreeDto|ModelInterface $dto): mixed
    {
        $controller = $this->appController;
        $ari = $this->sm->phpariObject;

        $channel_id = $controller->getChannelID();
        if ($channel_id === null) {
            $controller->stasisLogger->alert(__CLASS__ . ": ignored, channel id not set ");
            return null;
        }

        $channelsApi = $ari->channels();

        $channelsApi->ring($channel_id);
        sleep(1);
        $channelsApi->answer($channel_id);
        $controller->stasisLogger->notice("channel_playback() play1 " . $channel_id);
        $channelsApi->play($channel_id, [self::SOUND_MVG_GREETING], null, null, null, "play2");

//        if ($controller->mvgRadApi->hasLastPin()) {
            $channelsApi->play($channel_id, [self::SOUND_MVG_LAST_PIN_IS], null, null, null, "play3");
//            MvgRadModule::sayDigits("123", $channel_id, $channelsApi);
//        }
        $channelsApi->play($channel_id, [self::SOUND_MVG_PIN_PROMPT], null, null, null, "play4");

        return $this->sm->done($this);
    }

    public function before(TransitionEvent $event): mixed
    {
//        $this->sm = $event->getStateMachine();

        // TODO: Implement begin() method.
        return null;
    }

    public function after(TransitionEvent $event): mixed
    {
        // TODO: Implement after() method.
        return null;
    }
}
