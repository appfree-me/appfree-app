<?php

namespace AppFree\MvgRad\States;

use AppFree\AppController;
use AppFree\MvgRad\Api\MvgRadApi;
use AppFree\MvgRad\MvgRadStateMachine;
use Finite\State\State;

class MvgRadState extends State
{
    protected MvgRadApi $mvgRadApi;
    protected MvgRadStateMachine $sm;
//    protected AppController $appController;

    public function __construct($name, $type = self::TYPE_NORMAL, array $transitions = array(), array $properties = array())
    {

        parent::__construct($name, $type, $transitions, $properties);
    }

    public function init(MvgRadStateMachine $stateMachineSample, MvgRadApi $mvgRadApi): void {
        $this->mvgRadApi = $mvgRadApi;
        $this->sm = $stateMachineSample;
    }
}
