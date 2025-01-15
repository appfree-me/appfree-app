<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad;

use AppFree\appfree\modules\Generic\States\ReadDtmfString;
use AppFree\appfree\modules\MvgRad\Api\MvgRadApi;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\appfree\modules\MvgRad\States\AusleiheAndOutputPin;
use AppFree\appfree\modules\MvgRad\States\Begin;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachineInterface;

class MvgRadStateMachineLoader
{
//    private ?Closure $beforeFn = null;
//    private ?Closure $afterFn = null;
//

public const  DTO = "dto";

    public static function definition(StateMachineInterface $myStateMachine, MvgRadApi $mvgRadApi, MvgRadModule $mvgRadModule)
    {
        // https://github.com/yohang/Finite/blob/master/docs/usage/symfony.rst

        return [

            // init function for all states unless otherwise indicated
            'init' => [$myStateMachine, $mvgRadApi, $mvgRadModule],
            'states' => [
                Begin::class => ['type' => StateInterface::TYPE_INITIAL,],

                // init function for this state
                ReadDtmfString::class => ['init' => [$myStateMachine]],
                AusleiheAndOutputPin::class => ['type' => StateInterface::TYPE_FINAL,],
            ],

            'transitions' => [
                self::nameTransition(Begin::class, ReadDtmfString::class) => ['from' => [Begin::class], 'to' => ReadDtmfString::class, "properties" => [self::DTO => null]],
                self::nameTransition(ReadDtmfString::class, AusleiheAndOutputPin::class) => ['from' => [ReadDtmfString::class], 'to' => AusleiheAndOutputPin::class,  "properties" => [self::DTO => null]],


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
