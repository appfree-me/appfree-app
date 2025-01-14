<?php

declare(strict_types=1);

namespace AppFree;


use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\ApplicationReplaced;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelStateChange;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Playback;

class MakeDto {


/*todo test schreiben der korrektheit eingabe=> ausgabe prüft */
    public static function make(\stdClass $data): AppFreeDto {

        $mapping = [
            "StasisStart" => StasisStart::class,
            "StasisEnd" => StasisEnd::class, // todo refactor to use Laravel hydrator?
            "ChannelDtmfReceived" => ChannelDtmfReceived::class,
            "ApplicationReplaced" => ApplicationReplaced::class,
            "ChannelStateChange" => ChannelStateChange::class,
            "PlaybackFinished" => PlaybackFinished::class
        ];

        if (isset($data->channel->id)) {
            $channel = $data?->chanel?->id ? new Channel($data->channel->id) : null;
            $digit = $data?->digit;

            $var = new $mapping[$data->type](...[$channel, $digit]); //todo fixme: it must be defined how each dto is instantiated

        } else if ($data->type === "PlaybackFinished") {
            $var = new PlaybackFinished(new Playback($data->playback->id));
         } else {
            throw new \Exception("Unknown Event: ". json_encode($data)); //todo fixme: every valid message must result in valid dto

        }
        print("Made DTO" . serialize($var));

        return $var;



    }
}
