<?php

declare(strict_types=1);

namespace AppFree;

use Finite\Exception\ObjectException;
use React\EventLoop\Loop;

class AppFree
{
    /**
     * @throws ObjectException
     */
    public static function app(AppController $app): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGINT, function ($signal, $info) use ($app) {
            $app->handler($signal, $info);
        });

        register_shutdown_function(function () use ($app) {
            $app->shutdown();
        });

        $app->start();
        Loop::get()->run();
    }
}
