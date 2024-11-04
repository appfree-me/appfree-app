<?php

declare(strict_types=1);

namespace AppFree;



use Finite\StatefulInterface;

class SampleObject implements StatefulInterface {
    public $hallo = "hallo";

    // should have properties hallo2, hallo3

    public function getFiniteState()
    {
        // TODO: Implement getFiniteState() method.
    }

    public function setFiniteState($state)
    {
        // TODO: Implement setFiniteState() method.
    }
}
