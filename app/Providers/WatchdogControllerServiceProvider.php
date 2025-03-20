<?php

declare(strict_types=1);

namespace AppFree\Providers;

use AppFree\Watchdog\WatchdogController;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;

class WatchdogControllerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WatchdogController::class, function ($app) {
            return new WatchdogController(
                $app->get(Logger::class),
                $app->get(PromiseInterface::class),
                Loop::get()
            );
        });
    }
}
