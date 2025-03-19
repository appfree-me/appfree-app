<?php

declare(strict_types=1);

namespace AppFree\Providers;

use AppFree\Constants;
use AppFree\Ari\PhpAri;
use AppFree\Ari\PhpAriConfig;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class PhpAriConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PhpAriConfig::class, function (Application $app) {

            return new PhpAriConfig();
        });
    }
}
