<?php

declare(strict_types=1);

namespace AppFree\Providers;

use AppFree\Ari\PhpAri;
use AppFree\Watchdog\WatchdogController;
use Evenement\EventEmitter;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;

class WatchdogControllerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WatchdogController::class, function ($app) {
            return new WatchdogController($app->get(EventEmitter::class), $app->get(PhpAri::class), $app->get(Logger::class), $app->get(Client::class));
        });
    }
}
