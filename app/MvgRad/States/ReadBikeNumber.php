<?php

declare (strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\MvgRadModule;
use stdClass;

class ReadBikeNumber extends MvgRadState implements MvgRadStateInterface
{
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

    public function onEvent(stdClass $eventData): mixed
    {
        print(__CLASS__ . "->onEvent(" . json_encode($eventData));
        if ($eventData->type === "ChannelDtmfReceived") {
            $this->addDtmf($eventData->digit);
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
