<?php

use AppFree\AppController;
use AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFreeCommands\Stasis\Objects\V1\Channel;
use MvgRad\Loader;
use phpari3\PhpAri;
use Swagger\Client\Api\ChannelsApi;

describe("appfree-mvgrad", function () {
//    it('state advances until OutputPin State', function () {
    it('state Begin plays greeting, last pin and pin prompt and calls done()', function () {
        $channel = new Channel("testchannel");

        $getMvgRadAusleiheTestDtos = function () use ($channel) {

            return [
                new StasisStart($channel),
            ];
        };
        $getMvgRadAusleiheBikeNumberEntryDtos = function () use ($channel) {
            return [
                new ChannelDtmfReceived($channel, "1"),
                new ChannelDtmfReceived($channel, "2"),
                new ChannelDtmfReceived($channel, "3"),
                new ChannelDtmfReceived($channel, "4"),
                new ChannelDtmfReceived($channel, "5"),
//                new StasisEnd($channel),
            ];
        };

        $app = new AppController("appfree");
        $channelsApiMock = Mockery::mock(ChannelsApi::class);


        $monologMock = Mockery::mock('overload:Monolog\Logger')->shouldIgnoreMissing();
        $ariEndpointMock = Mockery::mock('overload:GuzzleHttp\Client');
        $stasisClientMock = Mockery::mock('overload:React\Promise\PromiseInterface');
        $stasisLoopMock = Mockery::mock('overload:React\EventLoop\LoopInterface');

        // Setup 2.
        $mvgRadApiMock = Mockery::mock('overload:MvgRad\Api\MvgRadApi');
        $mvgRadApiMock->shouldReceive("doAusleihe")->once()->andReturn("999");
        /////

        $phpAriMock = Mockery::mock(PhpAri::class);
        $phpAriMock->logger = $monologMock;
        $phpAriMock->ariEndpoint = $ariEndpointMock;
        $phpAriMock->stasisClient = $stasisClientMock;
        $phpAriMock->stasisLoop = $stasisLoopMock;

        $phpAriMock->shouldReceive('channels')->andReturn($channelsApiMock);

        $sm = Loader::load($app);

        $app->start($phpAriMock, $sm);

        $channelsApiMock->shouldReceive("ring");
        $channelsApiMock->shouldReceive("answer")->once();

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === [\MvgRad\States\Begin::SOUND_MVG_GREETING];
        });

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === [\MvgRad\States\Begin::SOUND_MVG_LAST_PIN_IS];
        });

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === [\MvgRad\States\Begin::SOUND_MVG_PIN_PROMPT];
        });


        // should transition to ... / enter state ...
        foreach ($getMvgRadAusleiheTestDtos() as $dto) {
            print(serialize($dto) . "\n");
            $app->receive($dto);
//            sleep(1);
        }




        // 2. Test Ausleihe


        // Vorlesen der eingegebenen Radnummer
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/1"];
        });

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/2"];
        });

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/3"];
        });

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/4"];
        });

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/5"];
        });


        // PIN-Ausgabe
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/9"];
        });
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/9"];
        });
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/9"];
        });


        foreach ($getMvgRadAusleiheBikeNumberEntryDtos() as $dto) {
            print(serialize($dto) . "\n");
            $app->receive($dto);
        }

        // 3. Test PIN Ausgabe (entfällt weil ReadBikeNumber auch OutputPin miterledigt)



    });
});
