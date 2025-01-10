<?php
declare(strict_types=1);


namespace AppFree;

use AppFree\Ari\PhpAri;
use AppFree\MvgRad\Loader;
use Finite\Exception\ObjectException;

require("vendor/lelaurent/appfree-mvgrad/vendor/autoload.php");
class AppFree
{
    /**
     * @throws ObjectException
     */
    public static function app(): void
    {
        pcntl_async_signals(true);

        $appName = "appfree";
        $app = new AppController($appName);

        pcntl_signal(SIGINT, function ($signal, $info) use ($app) {
            $app->handler($signal, $info);
        });

        register_shutdown_function(function () use ($app) {
            $app->handler(SIGINT, []);
        });

        $sm = Loader::load($app);
        $app->start(new PhpAri($appName, $app), $sm);
        $app->stasisLoop->run();
    }
}
