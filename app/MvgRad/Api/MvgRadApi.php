<?php

namespace AppFree\MvgRad\Api;

class MvgRadApi
{
    public function __construct()
    {
    }

    public function doAusleihe(string $radnummer): string
    {
//        $this->stasisLogger->notice("Ausleihe Nummer $radnummer");
        return "7890";
    }
}
