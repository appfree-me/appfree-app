<?php

declare(strict_types=1);

namespace AppFree;

use AppFree\AppFreeCommands\AppFreeDto;
use AppFree\AppFreeCommands\Stasis\Events\V1\ApplicationReplaced;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelHangupRequest;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelStateChange;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished;
use AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackStarted;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Caller;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Playback;
use Exception;
use Monolog\Logger;

class MakeDto
{
    /*todo test schreiben der korrektheit eingabe=> ausgabe prüft */
    public const SPECIAL_NUMBER = "0890000";

    /**
     * Suppress all warnings from these rules.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function make(\stdClass $data): AppFreeDto
    {
        $mapping = [
            "StasisStart" => StasisStart::class,
            "StasisEnd" => StasisEnd::class, // todo refactor to use Laravel hydrator?
            "ChannelDtmfReceived" => ChannelDtmfReceived::class,
            "ApplicationReplaced" => ApplicationReplaced::class,
            "ChannelStateChange" => ChannelStateChange::class,
            "PlaybackStarted" => PlaybackStarted::class,
            "PlaybackFinished" => PlaybackFinished::class,
            "ChannelHangupRequest" => ChannelHangupRequest::class
        ];

        if (isset($data->channel->id)) {
            $channel = null;
            $caller = null;

            if ($data->channel) {
                $caller = new Caller($data->channel->caller->name, config('mvg.video_dreh') && $data->channel->caller->number !== self::SPECIAL_NUMBER ? 'videodreh' : $data->channel->caller->number);
                $channel = new Channel($data->channel->id, $caller);
            }

            $digit = property_exists($data, "digit") ? $data->digit : null;
            try {
                $var = new $mapping[$data->type](...[$channel, $digit]); //todo fixme: it must be defined how each dto is instantiated
            } catch (Exception $e) {
                /** @var Logger $resolve */
                $resolve = resolve(Logger::class);
                $resolve->error($e->getMessage() . ", context: " . serialize($data));
            }
        } elseif ($data->type === "PlaybackFinished") {
            $var = new PlaybackFinished(new Playback($data->playback->id));
        } elseif ($data->type === "PlaybackStarted") {
            $var = new PlaybackStarted(new Playback($data->playback->id));
        } elseif ($data->type === "ApplicationReplaced") {
            $var = new ApplicationReplaced();
        } else {
            throw new Exception("Unknown Event: " . json_encode($data)); //todo fixme: every valid message must result in valid dto

        }
        print("Made DTO" . serialize($var));

        return $var;
    }
}
