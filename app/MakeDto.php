<?php

declare(strict_types=1);

namespace AppFree;


use AppFreeCommands\AppFreeDto;
use AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFreeCommands\Stasis\Events\V1\ChannelStateChange;
use AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFreeCommands\Stasis\Objects\V1\Channel;
use Swagger\Client\Model\ApplicationReplaced;
use Swagger\Client\Model\ModelInterface;

class MakeDto {


/*todo test schreiben der korrektheit eingabe=> ausgabe prüft */
    public static function make(\stdClass $data): AppFreeDto|ModelInterface {

        $mapping = [
            "StasisStart" => StasisStart::class,
            "StasisEnd" => StasisEnd::class, // todo refactor to use Laravel hydrator?
            "ChannelDtmfReceived" => ChannelDtmfReceived::class,
            "ApplicationReplaced" => ApplicationReplaced::class,
            "ChannelStateChange" => ChannelStateChange::class
        ];

        if (isset($data->channel->id)) {
            $channel = new Channel($data->channel->id);

            $var = new $mapping[$data->type]($channel, $data->digit ?? null); //todo fixme
            print("Made DTO" .  serialize($var));

            return $var;
         } else {
            return new ApplicationReplaced([]); //todo fixme

        }




    }
}
