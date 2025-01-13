<?php

namespace My\AwesomeBundle\EventListener;

use Finite\Event\TransitionEvent;

class MvgRadStateMachineTransitionListener
{
    /**
     * @param TransitionEvent $event
     */
    public function someEvent(TransitionEvent $event)
    {

        $entity = $event->getStateMachine()->getObject();
        $params = $event->getProperties();

        $entity->setSomething($params['something']);
    }
}
