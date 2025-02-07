<?php

namespace AppFree\appfree\modules\MvgRad\Api\Prod;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use AppFree\appfree\modules\MvgRad\AppFreeStateMachine;
use Finite\StateMachine\StateMachineInterface;
use RuntimeException;

class MvgRadApi implements MvgRadApiInterface
{
    public const API_ID = "prod";

    public function __construct(private AppFreeStateMachine $sm)
    {
    }

    public function doAusleihe(string $radnummer): string
    {
//        $this->stasisLogger->notice("Ausleihe Nummer $radnummer");
        return "999";
    }

    public function getAusleiheRadnummer(): ?string
    {
        return null;
    }

    public function getPin(): ?string
    {
        return "999";
    }

    public function doRueckgabe(): ?string
    {
        throw new RuntimeException("Prod Endpoint bei MVG existiert nicht");
    }
}
