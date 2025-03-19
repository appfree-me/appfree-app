<?php

use AppFree\Constants;
use AppFree\appfree\modules\MvgRad\Api\Prod\MvgRadApi;
use AppFree\appfree\modules\MvgRad\States\Begin;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Caller;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Playback;
use AppFree\Ari\PhpAri;
use React\EventLoop\Loop;
use Swagger\Client\Api\ChannelsApi;

describe("appfree-mvgrad sample flow", function () {
    beforeEach(function () {
        //todo should be beforeAll
        config()->set("app.authenticate", false);
        config()->set("app.mvg-rad-api", "prod");
        //        require_once(__DIR__ . "/../../app/appfree/modules/MvgRad/Api/MvgRadApiInterface.php");
    });

    //    it('state advances until OutputPin State', function () {
    it('state Begin plays greeting, last pin and pin prompt and transitions to ReadBikeNumber, receives Bike Number, repeats it and does ausleihe and plays pin', function () {
        $channel = new Channel("testchannel", new Caller("017662328758", "017662328758"));
        $pinPromptPlaybackId = "xid";
        $lastOutputDigitPlaybackId = "xid2";

        $getMvgRadAusleiheTestDtos = function () use ($pinPromptPlaybackId, $channel) {
            return [
                new StasisStart($channel),
                new ChannelDtmfReceived($channel, "#"),
                new \AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished(new Playback("$pinPromptPlaybackId"))
            ];
        };
        $getMvgRadAusleiheBikeNumberEntryDtos = function () use ($lastOutputDigitPlaybackId, $channel) {
            return [
                new ChannelDtmfReceived($channel, "1"),
                new ChannelDtmfReceived($channel, "2"),
                new ChannelDtmfReceived($channel, "3"),
                new ChannelDtmfReceived($channel, "4"),
                new ChannelDtmfReceived($channel, "5"),
                new \AppFree\AppFreeCommands\Stasis\Events\V1\PlaybackFinished(new Playback($lastOutputDigitPlaybackId))

//                new StasisEnd($channel),
            ];
        };

        //        $x =app()->getBindings();
        //$app = App::make(AppController::class);
        //        $app = resolve(AppController::class);
        //        $app = new AppController();
        //        $mvgRadApiMock = Mockery::mock('overload:AppFree\appfree\modules\MvgRad\Api\Prod\MvgRadApi', \AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface::class);
        //        $mvgRadApiMock->shouldReceive("getAusleiheRadnummer")->andReturn(false);
        $mvgRadModuleMock = Mockery::mock(\AppFree\appfree\modules\MvgRad\Api\MvgRadModule::class);
        $phpAriMock = Mockery::mock(PhpAri::class);
        $channelsApiMock = Mockery::mock(ChannelsApi::class);
        $loopMock = Mockery::mock('overload:React\EventLoop\Loop')->shouldIgnoreMissing();
        $promiseMock = Mockery::mock('overload:React\Promise\PromiseInterface')->shouldIgnoreMissing();
        $conApiMock = Mockery::mock('overload:AppFree\appfree\ConvenienceApi')->makePartial();
        $conApiMock->shouldReceive("play")->andReturn($pinPromptPlaybackId);
        $conApiMock->shouldReceive("sayDigits")
            ->withArgs(function ($arg1) {
                $b = $arg1 === "12345";
                return $b;
            })
            ->andReturn($lastOutputDigitPlaybackId);

        //todo hier kommt die pin des ausleihvorgangs rein - wird momentan noch nicht in DB geschrieben - fix me
        $conApiMock->shouldReceive("sayDigits")->withArgs(function ($arg1) {
            return $arg1 === "";
        });

        //??
        $conApiMock->shouldReceive("sayDigits");


        $channelsApiMock->shouldReceive("callListWithHttpInfo");


        $this->instance(ChannelsApi::class, $channelsApiMock);
        $this->instance(\AppFree\appfree\modules\MvgRad\Api\MvgRadModule::class, $mvgRadModuleMock);
        $this->instance(\React\Promise\PromiseInterface::class, $promiseMock);
        $this->instance(Loop::class, $loopMock);
        //        $this->instance(MvgRadApi::class, $mvgRadApiMock);
        $this->instance(PhpAri::class, $phpAriMock);
        $phpAriMock->shouldReceive('channels')->andReturn($channelsApiMock);

        $app = resolve(AppController::class);
        $sm = resolve(\AppFree\appfree\modules\MvgRad\MvgRadStateMachine::class);
        $app->start();
        //        $monologMock = Mockery::mock('overload:Monolog\Logger')->shouldIgnoreMissing();
        //        $ariEndpointMock = Mockery::mock('overload:GuzzleHttp\Client');
        //        $stasisClientMock = Mockery::mock('overload:React\Promise\PromiseInterface');
        //        $stasisLoopMock = Mockery::mock('overload:React\EventLoop\LoopInterface');

        // Setup 2.
        $mockedReturnedPin = "999";

        $mvgRadModuleMock->shouldReceive("getLastPin")->andReturn("123");

        // OKASSERT
        //        $mvgRadApiMock->shouldReceive("doAusleihe")->once()->andReturn($mockedReturnedPin);
        /////
        //        $phpAriMock->logger = $monologMock;
        //        $phpAriMock->ariEndpoint = $ariEndpointMock;
        //        $phpAriMock->stasisClient = $stasisClientMock;
        //        $phpAriMock->stasisLoop = $stasisLoopMock;


        //        $sm = MvgRadStateMachineLoader::load($app);


        $app->start($phpAriMock);

        $channelsApiMock->shouldReceive("ring")->ordered(Begin::class);
        $channelsApiMock->shouldReceive("answer")->once()->ordered(Begin::class);


        // BEgrüssung

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            $b = $arg === [Begin::SOUND_MVG_GRUSS];
            return $b;
        })->ordered();//->ordered(Begin::class);

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            $b = $arg === [Begin::SOUND_MVG_LAST_PIN_IS];
            return $b;
        })->ordered();//->once()->ordered(Begin::class);

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            $b = $arg === [Begin::SOUND_MVG_PIN_PROMPT];
            return $b;
        })->ordered();//->once()->ordered(Begin::class);

        // Vorlesen Letzte Pin
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            $b = $arg === ["sound:digits/1"];
            return $b;
        });//->once()->ordered(Begin::class);
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            $b = $arg === ["sound:digits/2"];
            return $b;
        });//->once()->ordered(Begin::class);
        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            $b = $arg === ["sound:digits/3"];
            return $b;
        });//->once()->ordered(Begin::class);


        // 2. Test Ausleihe


        // Vorlesen der eingegebenen Radnummer (momentan weggefallen)

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/1"];
        })->ordered();

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/2"];
        })->ordered();

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/3"];
        })->ordered();

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/4"];
        })->ordered();

        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
            return $arg === ["sound:digits/5"];
        })->ordered();


        // 3. PIN-Ausgabe

        // Channels API wird wegen conApiMoc nicht mehr aufgerufen, deshalb kann sie hier nicht mehr getestet werden
        //        $channelsApiMock->shouldReceive("play")->withArgs(function ($arg1, $arg) {
        //            $b = $arg === ["sound:digits/9"];
        //            return $b;
        //        })->times(3);//->ordered(\AppFree\appfree\modules\MvgRad\States\AusleiheAndOutputPin::class);


        // Auflegen
        $channelsApiMock->shouldReceive("hangup");//->ordered(\AppFree\appfree\modules\MvgRad\States\AusleiheAndOutputPin::class);
        //            ->withArgs(function ($arg1, $arg) {
        //            return $arg === ["sound:digits/9"];
        //        })

        //        expect($sm->getCurrentState()->getName())->toBe(Begin::class);


        // should transition to ... / enter state ...
        foreach ($getMvgRadAusleiheTestDtos() as $dto) {
            print(serialize($dto) . "\n");
            $app->receive($dto);
            //            sleep(1);
        }
        //        expect($sm->getCurrentState()->getName())->toBe(ReadDtmfString::class);

        foreach ($getMvgRadAusleiheBikeNumberEntryDtos() as $dto) {
            print(serialize($dto) . "\n");
            $app->receive($dto);
        }
    });
});
