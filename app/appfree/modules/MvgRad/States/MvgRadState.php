<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\States;

use AppFree\appfree\modules\MvgRad\Api\MvgRadApi;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\appfree\modules\MvgRad\Interfaces\AppFreeStateInterface;
use AppFree\appfree\modules\MvgRad\MvgRadStateMachine;

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
