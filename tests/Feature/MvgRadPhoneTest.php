<?php

use AppFree\AppController;
use AppFree\appfree\modules\MvgRad\Api\MvgRadApi;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Caller;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Playback;
use AppFree\Ari\PhpAri;
use Swagger\Client\Api\ChannelsApi;

describe("appfree-mvgrad sample flow", function () {
//    it('state advances until OutputPin State', function () {
    it('state Begin plays greeting, last pin and pin prompt and transitions to ReadBikeNumber, receives Bike Number, repeats it and does ausleihe and plays pin', function () {
        $channel = new Channel("testchannel", new Caller("12", "12"));
        config()->set("app.authenticate", false);

        $getMvgRadAusleiheTestDtos = function () use ($channel) {
            return [
                new StasisStart($channel),
                new ChannelDtmfReceived($channel, "#"),
                new \AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished(new Playback("play4"))
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

//        $x =app()->getBindings();
//$app = App::make(AppController::class);
//        $app = resolve(AppController::class);
//        $app = new AppController();
        $mvgRadApiMock = Mockery::mock(MvgRadApi::class);
        $mvgRadModuleMock = Mockery::mock(\AppFree\appfree\modules\MvgRad\Api\MvgRadModule::class);
        $phpAriMock = Mockery::mock(PhpAri::class);
        $channelsApiMock = Mockery::mock(ChannelsApi::class);
        $loopMock = Mockery::mock('overload:React\EventLoop\Loop')->shouldIgnoreMissing();
        $promiseMock = Mockery::mock('overload:React\Promise\PromiseInterface')->shouldIgnoreMissing();


        $this->instance(ChannelsApi::class, $channelsApiMock);
        $this->instance(\AppFree\appfree\modules\MvgRad\Api\MvgRadModule::class, $mvgRadModuleMock);
        $this->instance(\React\Promise\PromiseInterface::class, $promiseMock);
        $this->instance(\React\EventLoop\Loop::class, $loopMock);
        $this->instance(MvgRadApi::class, $mvgRadApiMock);
        $this->instance(PhpAri::class, $phpAriMock);

        $app = resolve(AppController::class);
        $app->start();
//        $monologMock = Mockery::mock('overload:Monolog\Logger')->shouldIgnoreMissing();
//        $ariEndpointMock = Mockery::mock('overload:GuzzleHttp\Client');
//        $stasisClientMock = Mockery::mock('overload:React\Promise\PromiseInterface');
//        $stasisLoopMock = Mockery::mock('overload:React\EventLoop\LoopInterface');

        // Setup 2.
        $mockedReturnedPin = "999";

        $mvgRadModuleMock->shouldReceive("sayDigits");
        $mvgRadModuleMock->shouldReceive("hasLastPin")->andReturn(true);

        // OKASSERT
        $mvgRadApiMock->shouldReceive("doAusleihe")->once()->andReturn($mockedReturnedPin);
        /////
//        $phpAriMock->logger = $monologMock;
//        $phpAriMock->ariEndpoint = $ariEndpointMock;
//        $phpAriMock->stasisClient = $stasisClientMock;
//        $phpAriMock->stasisLoop = $stasisLoopMock;

        $phpAriMock->shouldReceive('channels')->andReturn($channelsApiMock);

//        $sm = MvgRadStateMachineLoader::load($app);


        $app->start($phpAriMock);

        $channelsApiMock->shouldReceive("ring");
        $channelsApiMock->shouldReceive("answer")->once();


        // BEgrüssung
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === [\AppFree\appfree\modules\MvgRad\States\Begin::SOUND_MVG_GREETING];
        })->ordered()->once();

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === [\AppFree\appfree\modules\MvgRad\States\Begin::SOUND_MVG_LAST_PIN_IS];
        })->ordered()->once();

        // Vorlesen Letzte Pin
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/1"];
        })->ordered()->once();
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/2"];
        })->ordered()->once();
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/3"];
        })->ordered()->once();

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === [\AppFree\appfree\modules\MvgRad\States\Begin::SOUND_MVG_PIN_PROMPT];
        })->ordered()->once();



        // 2. Test Ausleihe


        // Vorlesen der eingegebenen Radnummer (momentan weggefallen)

//        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
//            return $arg === ["sound:digits/1"];
//        })->ordered();
//
//        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
//            return $arg === ["sound:digits/2"];
//        })->ordered();
//
//        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
//            return $arg === ["sound:digits/3"];
//        })->ordered();
//
//        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
//            return $arg === ["sound:digits/4"];
//        })->ordered();
//
//        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
//            return $arg === ["sound:digits/5"];
//        })->ordered();


        // 3. PIN-Ausgabe
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/9"];
        })->ordered()->times(3);


        // Auflegen
        $channelsApiMock->shouldReceive("hangup")
//            ->withArgs(function ($arg1, $arg) {
//            return $arg === ["sound:digits/9"];
//        })
            ->ordered();


        // should transition to ... / enter state ...
        foreach ($getMvgRadAusleiheTestDtos() as $dto) {
            print(serialize($dto) . "\n");
            $app->receive($dto);
//            sleep(1);
        }

        foreach ($getMvgRadAusleiheBikeNumberEntryDtos() as $dto) {
            print(serialize($dto) . "\n");
            $app->receive($dto);
        }
    });
});
