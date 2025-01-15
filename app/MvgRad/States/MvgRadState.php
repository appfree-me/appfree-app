<?php

declare(strict_types=1);

namespace AppFree\MvgRad\States;

use AppFree\MvgRad\Api\MvgRadApi;
use AppFree\MvgRad\Api\MvgRadModule;
use AppFree\MvgRad\Interfaces\AppFreeStateInterface;
use AppFree\MvgRad\MvgRadStateMachine;
use Finite\State\State;

abstract class MvgRadState extends AppFreeState implements AppFreeStateInterface
{
    protected MvgRadApi $mvgRadApi;
    protected MvgRadStateMachine $sm;
    protected MvgRadModule $mvgRadModule;

    public function init(MvgRadStateMachine $stateMachineSample, MvgRadApi $mvgRadApi, MvgRadModule $mvgRadModule): void {
        $this->mvgRadModule =$mvgRadModule;
        $this->mvgRadApi = $mvgRadApi;
        $this->sm = $stateMachineSample;
    }


}
