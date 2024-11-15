<?php

namespace AppFree\MvgRad\States;

use app\MvgRadStasisAppController;

interface MvgRadStateInterface
{

    public function vorbedingung(): bool;
    public function begin(): mixed;

    public function onEvent(\stdClass $eventData): mixed;
}
