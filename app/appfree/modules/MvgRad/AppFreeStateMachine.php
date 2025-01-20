<?php
declare(strict_types=1);


namespace AppFree\appfree\modules\MvgRad;

use AppFree\appfree\StateMachineContext;
use Finite\StateMachine\StateMachine;

abstract class AppFreeStateMachine extends StateMachine
{

    private StateMachineContext $context;

    public function getContext(): StateMachineContext
    {
        return $this->context;
    }

    public function setContext(StateMachineContext $context): void {
        $this->context = $context;
    }
}
