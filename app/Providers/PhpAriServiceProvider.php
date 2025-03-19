<?php

declare(strict_types=1);

namespace AppFree\Providers;

use AppFree\Constants;
use AppFree\Ari\PhpAri;
use AppFree\Ari\PhpAriConfig;
use Evenement\EventEmitter;
use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;

class PhpAriServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PhpAri::class, function (Application $app) {
            $emitter = $app->get(EventEmitter::class);

            return new PhpAri("appfree-". config("app.env"), $emitter, $app->get(PhpAriConfig::class), $app->get(Client::class), $app->get(Logger::class)); //fixme
        });
    }
}
