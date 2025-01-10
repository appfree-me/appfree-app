<?php

namespace AppFree\MvgRad\Api;
use Swagger\Client\Api\ChannelsApi;

class MvgRadModule
{
//    private AppController $app;
    private MvgRadApi $mvgRadApi;

    function __construct()
    {
        $this->mvgRadApi = new MvgRadApi();
    }

    public static function sayDigits(string $digitString, $channelID, ChannelsApi $channelsApi): void
    {
        foreach (str_split($digitString) as $digit) {
            $channelsApi->play($channelID, ["sound:digits/$digit"], null, null, null);
        }
    }

    public function hasLastPin(): bool
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
