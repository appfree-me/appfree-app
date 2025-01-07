<?php
declare(strict_types=1);


namespace AppFree;

use Finite\Exception\ObjectException;

require("vendor/lelaurent/appfree-mvgrad/vendor/autoload.php");
class AppFree
{
    /**
     * @throws ObjectException
     */
    public static function app()
    {
        pcntl_async_signals(true);

        $app = new AppController("appfree");

        pcntl_signal(SIGINT, function ($signal, $info) use ($app) {
            $app->handler($signal, $info);
        });

        register_shutdown_function(function () use ($app) {
            $app->handler(SIGINT, []);
        });

        $app->start();
        $app->stasisLoop->run();
    }
}
