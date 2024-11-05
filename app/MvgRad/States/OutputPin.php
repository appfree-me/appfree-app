<?php
declare(strict_types=1);

namespace AppFree\MvgRad\States;

use Finite\State\State;

class OutputPin extends MvgRadState implements MvgRadStateInterface {
    public function vorbedingung(): bool
    {
        // TODO: Implement vorbedingung() method.
        return true;
    }

    public function begin(): mixed
    {
        // TODO: Implement ausfuehren() method.
        return null;
    }

    public function onEvent(\stdClass $event): mixed
    {
        // TODO: Implement event() method.
        return null;
    }
}
