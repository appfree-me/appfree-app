<?php
declare(strict_types=1);


namespace AppFree\appfree\modules\MvgRad;

use AppFree\appfree\StateMachineContext;
use Finite\StateMachine\StateMachine;

abstract class AppFreeStateMachine extends StateMachine
{

    public function getContext(): StateMachineContext
    {
        return $this->object;
    }
}
