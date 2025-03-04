<?php

declare(strict_types=1);

namespace AppFree;

use Finite\StatefulInterface;

class StatefulObject implements StatefulInterface
{
    private ?string $state = null;

    public function __construct()
    {
    }

    public function getFiniteState()
    {
        return $this->state;
    }

    public function setFiniteState($state)
    {
        $this->state = $state;
    }
}
