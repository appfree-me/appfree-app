<?php

namespace AppFree\appfree\modules\MvgRad\Api\Prod;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use Finite\StateMachine\StateMachineInterface;

class MvgRadApi implements MvgRadApiInterface
{
    public const API_ID = "prod";

    public function __construct(private StateMachineInterface $sm)
    {
    }

    public function doAusleihe(string $radnummer): string
    {
//        $this->stasisLogger->notice("Ausleihe Nummer $radnummer");
        return "999";
    }

    public function isAusleiheRunning(): bool
    {
        return true;
    }

    public function getPin(): ?string
    {
        return "999";
    }
}
