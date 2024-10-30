<?php

namespace AppFree;
class MvgRadModule
{
    private MvgRadStasisAppController $app;
    private MvgRadApi $mvgRadApi;

    function __construct(MvgRadStasisAppController $app)
    {
        $this->mvgRadApi = new MvgRadApi();
        $this->app = $app;
    }

    public function hasLastPin(): bool
    {
        return true;
    }

    public function part2(string $dtmfSequence): void
    {
        // DTMF should now be available
        $pin = $this->mvgRadApi->doAusleihe($dtmfSequence);
        $this->app->sayDigits($pin);
    }
}
