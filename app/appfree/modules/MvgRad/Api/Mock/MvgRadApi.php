<?php

namespace AppFree\appfree\modules\MvgRad\Api\Mock;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;

class MvgRadApi implements MvgRadApiInterface
{
    public function __construct()
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
