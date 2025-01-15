<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\Generic\States;

use AppFree\appfree\modules\MvgRad\Interfaces\AppFreeStateInterface;
use AppFree\appfree\modules\MvgRad\States\AppFreeState;
use Finite\StateMachine\StateMachineInterface;

abstract class GenericState extends AppFreeState implements AppFreeStateInterface
{
    protected StateMachineInterface $sm;

    public function init(StateMachineInterface $stateMachineSample): void
    {
        $this->sm = $stateMachineSample;
    }
}
