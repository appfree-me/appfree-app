<?php

return [
    AppFree\Providers\AppControllerServiceProvider::class,
    AppFree\Providers\PhpAriServiceProvider::class,
    AppFree\Providers\PhpAriConfigServiceProvider::class,
    AppFree\Providers\GuzzleHttpServiceProvider::class,
    AppFree\Providers\LoggerServiceProvider::class,
//    AppFree\Providers\StateMachineInterfaceServiceProvider::class,
    AppFree\Providers\MvgRadStateMachineServiceProvider::class,
    AppFree\Providers\StasisClientServiceProvider::class,

];
