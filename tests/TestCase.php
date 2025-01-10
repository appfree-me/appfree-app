<?php

namespace Tests;

use AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFreeCommands\Stasis\Objects\V1\Channel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{


    public function getMvgRadAusleiheTestDtos() {

        $channel = new Channel("testchannel");

        return [
            new StasisStart($channel),
            new ChannelDtmfReceived($channel, "1"),
            new ChannelDtmfReceived($channel, "2"),
            new ChannelDtmfReceived($channel, "3"),
            new ChannelDtmfReceived($channel, "4"),
            new ChannelDtmfReceived($channel, "5"),
            new StasisEnd($channel),
        ];
    }

    public function mvgRadTest() {
        expect(2).toBe(2);
    }



    //
}
