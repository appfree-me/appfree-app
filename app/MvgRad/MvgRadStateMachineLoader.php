<?php

declare(strict_types=1);

namespace AppFree\MvgRad;

use AppFree\AppController;
use AppFree\MvgRad\Api\MvgRadApi;
use AppFree\MvgRad\States\Begin;
use AppFree\MvgRad\States\OutputPin;
use AppFree\MvgRad\States\ReadBikeNumber;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachineInterface;

class MvgRadStateMachineLoader
{
//    private ?Closure $beforeFn = null;
//    private ?Closure $afterFn = null;
//

    public static function definition(StateMachineInterface $myStateMachine, MvgRadApi $mvgRadApi)
    {
        // https://github.com/yohang/Finite/blob/master/docs/usage/symfony.rst

        return [
            'init' => [$myStateMachine, $mvgRadApi],
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
//                'before' => [
//                    [
//                        'do' => [$this, 'beforeTransition'],
//                    ],
//                ],
//                'after' => [
//                    [
//                        'do' => [$this, 'afterTransition'],
//                    ],
//                ]
            ]
        ];
    }

//    public function beforeTransition(StatefulInterface $a, TransitionEvent $e): void
//    {
//        echo 'before transition ' . $e->getTransition()->getName(), "\n";
//
//        $this->sm->getCurrentState()->before($e);
//    }
//
//    public function afterTransition(StatefulInterface $a, TransitionEvent $e): void
//    {
//        echo 'after transition ' . $e->getTransition()->getName(), "\n";
//
//        $this->sm->getCurrentState()->after($e);
//    }


    public static function nameTransition(string $class1, string $class2): string
    {
        return implode(", ", [$class1, $class2]);
    }
}
