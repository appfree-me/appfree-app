<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad;

use AppFree\appfree\modules\Generic\States\ReadDtmfString;
use AppFree\appfree\modules\MvgRad\Api\MvgRadApiInterface;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\appfree\modules\MvgRad\Api\Prod\MvgRadApi;
use AppFree\appfree\modules\MvgRad\States\AusleiheAndOutputPin;
use AppFree\appfree\modules\MvgRad\States\Begin;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachineInterface;

class MvgRadStateMachineLoader extends AppFreeStateMachineLoader
{
    public const  DTO = "dto";

    public static function definition(StateMachineInterface $myStateMachine, MvgRadApiInterface $mvgRadApi, MvgRadModule $mvgRadModule)
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
                // "dto" property will contain the dto to be passed to the next state later
                self::nameTransition(Begin::class, ReadDtmfString::class) => ['from' => [Begin::class], 'to' => ReadDtmfString::class, "properties" => [self::DTO => null]],
                self::nameTransition(ReadDtmfString::class, AusleiheAndOutputPin::class) => ['from' => [ReadDtmfString::class], 'to' => AusleiheAndOutputPin::class,  "properties" => [self::DTO => null]],
            ],
        ];
    }

    public static function nameTransition(string $class1, string $class2): string
    {
        return implode(", ", [$class1, $class2]);
    }
}
