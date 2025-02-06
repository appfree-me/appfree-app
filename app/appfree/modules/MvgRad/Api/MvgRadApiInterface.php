<?php

namespace AppFree\appfree\modules\MvgRad\Api;

use Finite\StateMachine\StateMachineInterface;

interface MvgRadApiInterface
{
    public function __construct(StateMachineInterface $sm);

    public function doAusleihe(string $radnummer): ?string;
    public function isAusleiheRunning(): bool;
    public function getPin(): ?string;
}
