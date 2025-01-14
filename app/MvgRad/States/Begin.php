<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\AppController;
use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\Ari\PhpAri;
use Finite\Event\TransitionEvent;
use Monolog\Logger;
use Swagger\Client\Model\ModelInterface;

class Begin extends MvgRadState
{
    const SOUND_MVG_GREETING = 'sound:mvg-greeting';
    const SOUND_MVG_LAST_PIN_IS = 'sound:mvg-last-pin-is';
    const SOUND_MVG_PIN_PROMPT = 'sound:mvg-pin-prompt';

    public function vorbedingung(): bool
    {
        // keine vorbedingung

        return true;
    }


    public function run(): \Generator {
        $ari = resolve(PhpAri::class);
        $logger = resolve(Logger::class);

        $dto = yield ;

        $channel_id = $dto->getChannel()?->id;
        if ($channel_id === null) {
            $logger->alert(__CLASS__ . ": ignored, channel id not set ");
            return;
        }


        $channelsApi = $ari->channels();

        $channelsApi->ring($channel_id);
        sleep(1);
        $channelsApi->answer($channel_id);
        $logger->notice("channel_playback() play1 " . $channel_id);
        $channelsApi->play($channel_id, [self::SOUND_MVG_GREETING], null, null, null, "play2");

//        if ($controller->mvgRadApi->hasLastPin()) {
        $channelsApi->play($channel_id, [self::SOUND_MVG_LAST_PIN_IS], null, null, null, "play3");
//            MvgRadModule::sayDigits("123", $channel_id, $channelsApi);
//        }
        $channelsApi->play($channel_id, [self::SOUND_MVG_PIN_PROMPT], null, null, null, "play4");

        $dto = yield "expected" => PlaybackFinished::class;
        if ($dto instanceof PlaybackFinished) {
            yield function() {
                $this->sm->done(ReadBikeNumber::class, null);
            };
        } else {
            throw new \Exception("Wrong DTO => " . $dto::class);
        }



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
