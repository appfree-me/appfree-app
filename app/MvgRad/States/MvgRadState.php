<?php

declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\MvgRad\Api\MvgRadApi;
use AppFree\MvgRad\Interfaces\AppFreeStateInterface;
use AppFree\MvgRad\MvgRadStateMachine;
use Finite\State\State;

abstract class MvgRadState extends AppFreeState implements AppFreeStateInterface
{
    protected MvgRadApi $mvgRadApi;
    protected MvgRadStateMachine $sm;

    public function init(MvgRadStateMachine $stateMachineSample, MvgRadApi $mvgRadApi): void {
        $this->mvgRadApi = $mvgRadApi;
        $this->sm = $stateMachineSample;
    }


}
