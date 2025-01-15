<?php

use AppFree\appfree\modules\MvgRad\States\AppFreeState;
use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;

describe("appfree generator logic", function () {
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

        $channel = new Channel("testchannel");
        $inputDtos = [
            new StasisStart($channel),
            new ChannelDtmfReceived($channel, "#"),
            new StasisEnd($channel),
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

        $channel = new Channel("testchannel");
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
