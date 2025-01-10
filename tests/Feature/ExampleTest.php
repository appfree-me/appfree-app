<?php

use AppFree\AppController;
use AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFreeCommands\Stasis\Objects\V1\Channel;
use MvgRad\Loader;
use phpari3\PhpAri;
use Swagger\Client\Api\ChannelsApi;

describe("appfree-mvgrad", function () {
//    it('state advances until OutputPin State', function () {
    it('state Begin plays greeting, last pin and pin prompt and calls done()', function () {
        $getMvgRadAusleiheTestDtos = function () {
            $channel = new Channel("testchannel");

            return [
                new StasisStart($channel),
//                new ChannelDtmfReceived($channel, "1"),
//                new ChannelDtmfReceived($channel, "2"),
//                new ChannelDtmfReceived($channel, "3"),
//                new ChannelDtmfReceived($channel, "4"),
//                new ChannelDtmfReceived($channel, "5"),
//                new StasisEnd($channel),
            ];
        };

        $app = new AppController("appfree");
        $channelsApiMock = Mockery::mock(ChannelsApi::class);

        $monologMock = Mockery::mock('overload:Monolog\Logger')->shouldIgnoreMissing();
        $ariEndpointMock = Mockery::mock('overload:GuzzleHttp\Client');
        $stasisClientMock = Mockery::mock('overload:React\Promise\PromiseInterface');
        $stasisLoopMock = Mockery::mock('overload:React\EventLoop\LoopInterface');

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


        foreach ($getMvgRadAusleiheTestDtos() as $dto) {
            print(serialize($dto) . "\n");
            $app->receive($dto);
            sleep(1);
        }
    });
});
