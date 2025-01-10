<?php
declare(strict_types=1);


namespace AppFree\MvgRad;

use AppFree\Ari\PhpAri;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;

class MyStateMachine extends StateMachine
{
    public PhpAri $phpariObject;

    public function done(StateInterface $state): void
    {
        $this->apply($this->getNextTransition($state)); // nächste transition finden
        // todo lp: mit charlotte besprechen
    }

    public function getNextTransition($state)
    {
        $fromTransitions =$this->getCurrentState()->getTransitions();

        //fixme - transition muss abhängig sein von result des letzten zustands
        if (count($fromTransitions) !== 1) {
            throw new \Exception("Next state not defined");
        }

        return $fromTransitions[0];
    }

}
