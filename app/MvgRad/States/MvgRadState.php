<?php

namespace AppFree\MvgRad\States;

use AppFree\MvgRadApi;
use AppFree\MvgRadStasisAppController;
use AppFree\StateMachineSample;
use Finite\State\State;

class MvgRadState extends State
{
    protected MvgRadApi $mvgRadApi;
    protected StateMachineSample $stateMachineSample;

    public function __construct($name, $mvgRadApi,  StateMachineSample $stateMachineSample, $type = self::TYPE_NORMAL, array $transitions = array(), array $properties = array())
    {
        $this->mvgRadApi = $mvgRadApi;
        $this->stateMachineSample = $stateMachineSample;
        parent::__construct($name, $type, $transitions, $properties);
    }
}
