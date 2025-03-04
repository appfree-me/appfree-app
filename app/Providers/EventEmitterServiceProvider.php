<?php

declare(strict_types=1);

namespace AppFree\Providers;

use Evenement\EventEmitter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class EventEmitterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventEmitter::class, function (Application $app) {
            return new EventEmitter();
        });
    }
}
