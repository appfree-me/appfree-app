<?php

declare(strict_types=1);

namespace AppFree;


use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelStateChange;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;
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
