<?php
declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\appfree\modules\Generic\States\ReadDtmfString;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\AppFreeCommands\AppFree\Commands\V1\ReadDtmfStringFunctionCommand;
use AppFree\AppFreeCommands\MvgRad\Commands\V1\MvgRadAusleiheCommand;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;
use AppFree\Ari\PhpAri;
use Finite\Event\TransitionEvent;
use Monolog\Logger;

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

    public function run(): \Generator
    {
        $ari = resolve(PhpAri::class);
        $logger = resolve(Logger::class);

        $dto = yield;

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

        if ($this->mvgRadModule->hasLastPin()) {
            $channelsApi->play($channel_id, [self::SOUND_MVG_LAST_PIN_IS], null, null, null, "play3");
            MvgRadModule::sayDigits("123", $channel_id, $channelsApi);
        }
        $channelsApi->play($channel_id, [self::SOUND_MVG_PIN_PROMPT], null, null, null, "play4");

        yield "expect" => PlaybackFinished::class;

        yield "call" => function () {
            $this->sm->done(ReadDtmfString::class,
                new ReadDtmfStringFunctionCommand(function (array $dtmfSequence) {
                    $this->sm->done(AusleiheAndOutputPin::class, new MvgRadAusleiheCommand(implode($dtmfSequence)));
                }));
        };
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
