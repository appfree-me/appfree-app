<?php

declare(strict_types=1);

namespace AppFree\MvgRad;

use AppFree\AppController;
use AppFree\MvgRad\Api\MvgRadApi;
use AppFree\MvgRad\States\Begin;
use AppFree\MvgRad\States\OutputPin;
use AppFree\MvgRad\States\ReadBikeNumber;
use Closure;
use Finite\Event\TransitionEvent;
use Finite\State\StateInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;

class Loader
{
    private array $definition;
    private ?Closure $beforeFn = null;
    private ?Closure $afterFn = null;
    private StateMachine $sm;
    private AppController $appController;

    private function __construct(MyStateMachine $myStateMachine, MvgRadApi $mvgRadApi, AppController $appController)
    {
        // https://github.com/yohang/Finite/blob/master/docs/usage/symfony.rst

        $this->definition = [
//            'class' => MvgRadState::class, // # Required, FQCN of your class
//            'graph' => MvgRadApp::class, // Name of your graph, keep default if using a single graph
//            'property_path' => 'paymentStatus', // The property of your class used to store the state
            'init' => [$myStateMachine, $mvgRadApi, $appController],
            'states' => [
                Begin::class => ['type' => StateInterface::TYPE_INITIAL,],
                ReadBikeNumber::class => [],
                OutputPin::class => ['type' => StateInterface::TYPE_FINAL,],
            ],

            'transitions' => [
                self::nameTransition(Begin::class, ReadBikeNumber::class) => ['from' => [Begin::class], 'to' => ReadBikeNumber::class],
                self::nameTransition(ReadBikeNumber::class, OutputPin::class) => ['from' => [ReadBikeNumber::class], 'to' => OutputPin::class],


//                self::nameTransition(ReadBikeNumber::class, ReadDtmfState::class) => ['from' => [ReadBikeNumber::class], 'to' => ReadDtmfState::class],
//                self::nameTransition(ReadDtmfState::class, ReadBikeNumber::class) => ['from' => [ReadDtmfState::class], 'to' => ReadBikeNumber::class],
            ],
            'callbacks' => [
                'before' => [
                    [
                        'do' => [$this, 'beforeTransition'],
                    ],
                ],
                'after' => [
                    [
                        'do' => [$this, 'afterTransition'],
                    ],
                ]
            ]
        ];
    }

    public function beforeTransition(StatefulInterface $a, TransitionEvent $e): void
    {
        echo 'before transition ' . $e->getTransition()->getName(), "\n";

        $this->sm->getCurrentState()->before($e);
    }

    public function afterTransition(StatefulInterface $a, TransitionEvent $e): void
    {
        echo 'after transition ' . $e->getTransition()->getName(), "\n";

        $this->sm->getCurrentState()->after($e);
    }

    public static function load(AppController $appController, ?Closure $beforeFn = null, ?Closure $afterFn = null): StateMachine
    {
        $myStateMachine = new MyStateMachine($appController);

        $self = new self($myStateMachine, new MvgRadApi(), $appController);
        $loader = new MyArrayLoader($self->definition);
        $loader->load($myStateMachine);

        $self->sm = $myStateMachine;
        $self->appController = $appController;

        $self->beforeFn = $beforeFn;
        $self->afterFn = $afterFn;

        return $myStateMachine;
    }

    public static function nameTransition(string $class1, string $class2): string
    {
        return implode(", ", [$class1, $class2]);
    }
}
