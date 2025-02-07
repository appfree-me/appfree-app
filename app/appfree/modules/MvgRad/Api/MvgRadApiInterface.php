<?php

namespace AppFree\appfree\modules\MvgRad\Api;

use AppFree\appfree\modules\MvgRad\AppFreeStateMachine;

interface MvgRadApiInterface
{
//    public function __construct(AppFreeStateMachine $sm);

    public function doAusleihe(string $radnummer): ?string;

    public function getAusleiheRadnummer(): ?string;

    public function getPin(): ?string;

    public function doRueckgabe(): ?string;
}
