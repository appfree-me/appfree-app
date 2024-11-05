<?php

declare (strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\MvgRadModule;
use AppFree\MvgRadStasisAppController;
use AppFree\StateMachineSample;
use Devristo\Phpws\Messaging\WebSocketMessage;
use Finite\State\State;

class ReadBikeNumber  extends MvgRadState implements MvgRadStateInterface{
    private array $dtmfSequence = [];

    public function handleAusleihe(): void
    {
        $radnummer = implode($this->dtmfSequence);

        MvgRadModule::sayDigits($radnummer, $this->stateMachineSample);
        $pin = $this->mvgRadApi->doAusleihe($radnummer);
        MvgRadModule::sayDigits($pin, $this->stateMachineSample);
        $this->stateMachineSample->done(ReadBikeNumber::class);
    }

//    public function onDtmfReceived($event) {
//
//        switch ($event->digit) {
//            case "*":
//                $this->dtmfSequence = [];
//                break;
//            case "#":
//                $this->begin();
//                break;
//            default:
//                break;
//        }
//
//    }

    public function vorbedingung(): bool
    {
        // TODO: Implement vorbedingung() method.
        return true;
    }

    public function onEvent(\stdClass $event): mixed
    {
        print(__CLASS__ . "->onEvent(".json_encode($event));
        if ($event->type === "ChannelDtmfReceived"){
            $this->addDtmf($event->digit);
        }
        if (count($this->dtmfSequence) === 5) {
            $this->handleAusleihe();
        }

        return null;
    }
    public function addDtmf(string $digit): void
    {
        $this->dtmfSequence[] = $digit;
    }

    public function begin(): mixed
    {
        // TODO: Implement begin() method.
        return null;
    }
}
