<?php

use AppFree\AppFreeCommands\Stasis\Events\V1\ChannelDtmfReceived;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisEnd;
use AppFree\AppFreeCommands\Stasis\Events\V1\StasisStart;
use AppFree\AppFreeCommands\Stasis\Objects\V1\Channel;
use AppFree\MvgRad\States\AppFreeState;

describe("appfree generator logic", function () {
//    it('state advances until OutputPin State', function () {
    it('has correct logic when calling the generator', function () {
        global $calledExitFn;
        $calledExitFn = false;

        $class = new class("testname") extends AppFreeState {
            public function vorbedingung(): bool
            {
                // TODO: Implement vorbedingung() method.
            }

            public function before(\Finite\Event\TransitionEvent $event): mixed
            {
                // TODO: Implement before() method.
            }

            public function after(\Finite\Event\TransitionEvent $event): mixed
            {
                // TODO: Implement after() method.
            }

            public function run(): \Generator
            {
                $dto = yield;
                expect($dto)->toBeInstanceOf(StasisStart::class);

                $dto = yield "expect" => StasisEnd::class;
                expect($dto)->toBeInstanceOf(StasisEnd::class);
                yield "call" => function () {
                    global $calledExitFn;
                    $calledExitFn = true;
                };
//            yield;
//            expect(true)->toBeFalse("After returning the exit function, the generator should not be called again.");
            }
        };

        $channel = new Channel("testchannel");
        $inputDtos = [
            new StasisStart($channel),
            new ChannelDtmfReceived($channel, "*"),
            new ChannelDtmfReceived($channel, "2"),
            new ChannelDtmfReceived($channel, "2"),
            new ChannelDtmfReceived($channel, "#"),
            new StasisEnd($channel),
        ];

        foreach ($inputDtos as $dto) {
            $class->onEvent($dto);
        }

        expect($calledExitFn)->toBeTrue("AppFreeState should call the exit function provided by the generator.");
    });
});
