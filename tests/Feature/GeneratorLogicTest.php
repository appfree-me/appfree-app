<?php

use AppFree\appfree\modules\MvgRad\States\AppFreeState;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Caller;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;


describe("appfree generator logic", function () {
    beforeEach(function () {
        config()->set("app.authenticate", false);

        $this->caller = new Caller("c1", "c1");
        $this->channel = new Channel("testchannel", $this->caller);
    });
//    it('state advances until OutputPin State', function () {
    it('expect works as first statement and yield "call" works as last statement', function () {
        global $calledProvidedFn;
        $calledProvidedFn = false;

        $class = new class("testname") extends AppFreeState {


            public function run(): \Generator
            {
                $dto = yield "expect" => StasisEnd::class;
                expect($dto)->toBeInstanceOf(StasisEnd::class);
                $dto = yield "call" => function () {
                    global $calledProvidedFn;
                    $calledProvidedFn = true;
                };
                expect($dto)->toBeNull();
            }
        };


        $inputDtos = [
            new StasisStart($this->channel),
            new ChannelDtmfReceived($this->channel, "#"),
            new StasisEnd($this->channel),
        ];

        foreach ($inputDtos as $dto) {
            $class->onEvent($dto);
        }

        expect($calledProvidedFn)->toBeTrue("AppFreeState should call the function provided by the generator.");
    });
    it('processing works after call', function () {
        $class = new class("testname") extends AppFreeState {

            public function run(): \Generator
            {
                $dto = yield "expect" => StasisEnd::class;
                $dto = yield "call" => function () {
                };
                expect($dto)->toBeInstanceOf(ChannelDtmfReceived::class);
            }
        };

        $channel = new Channel("testchannel", $this->caller);
        $inputDtos = [
            new StasisStart($channel),
            new StasisEnd($channel),
            new ChannelDtmfReceived($channel, "*"),
        ];

        foreach ($inputDtos as $dto) {
            $class->onEvent($dto);
        }
    });
});
