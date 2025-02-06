<?php

namespace AppFree\appfree\modules\MvgRad\Api;

interface MvgRadApiInterface
{
    public function doAusleihe(string $radnummer): string;
    public function isAusleiheRunning(): bool;
    public function getPin(): ?string;
}
