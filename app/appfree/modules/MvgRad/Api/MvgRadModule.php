<?php

namespace AppFree\appfree\modules\MvgRad\Api;

use Swagger\Client\Api\ChannelsApi;
use Swagger\Client\ApiException;

class MvgRadModule
{
    /**
     * Playback some digits and return the playback id of the last played back digit.
     *
     * @param string $digitString
     * @param $channelID
     * @param ChannelsApi $channelsApi
     * @return string
     * @throws ApiException
     */
    public static function sayDigits(string $digitString, $channelID, ChannelsApi $channelsApi): string
    {
        $playbackId = uniqid(__METHOD__);
        foreach (str_split($digitString) as $digit) {
            $channelsApi->play($channelID, ["sound:digits/$digit"], null, null, null, $playbackId);
        }
        return $playbackId;
    }

    public static function hasLastPin(): bool
    {
        return true;
    }

//    public function part2(string $dtmfSequence): void
//    {
//        // DTMF should now be available
//        $pin = $this->mvgRadApi->doAusleihe($dtmfSequence);
//        self::sayDigits($pin, $this->app);
//    }
}
