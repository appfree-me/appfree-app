<?php
declare(strict_types=1);


namespace AppFree;

class AppFree
{
    public static function app()
    {
        pcntl_async_signals(true);

        $sm = new StateMachineSample("appfree");


        pcntl_signal(SIGINT, function ($signal, $info) use ($sm) {
            $sm->handler($signal, $info);
        });

        $sm->start();
        $sm->init();
        $sm->stasisLoop->run();
    }
}
