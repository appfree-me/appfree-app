<?php

namespace AppFree\appfree\modules\MvgRad\Api;

use AppFree\appfree\modules\MvgRad\AppFreeStateMachine;

interface MvgRadApiInterface
{
    public function isMock(): bool;

    public function doAusleihe(string $radnummer, ?string $mockPin): ?string;

    public function getAusleiheRadnummer(): ?string;

    public function getPin(): ?string;

    public function doRueckgabe(): ?string;

    public function init(): void;
}
