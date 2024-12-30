<?php

namespace AppFree;
class MvgRadModule
{
    private AppController $app;
    private MvgRadApi $mvgRadApi;

    function __construct(AppController $app)
    {
        $this->mvgRadApi = new MvgRadApi();
        $this->app = $app;
    }

    public static function sayDigits(string $digitString, AppController $instance): void
    {
        foreach (str_split($digitString) as $digit) {
            $instance->phpariObject->channels()->play($instance->getChannelID(), ["sound:digits/$digit"], null, null, null);
        }
    }

    public function hasLastPin(): bool
    {
        return true;
    }

    public function part2(string $dtmfSequence): void
    {
        // DTMF should now be available
        $pin = $this->mvgRadApi->doAusleihe($dtmfSequence);
        self::sayDigits($pin, $this->app);
    }
}
