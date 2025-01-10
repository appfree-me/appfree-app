<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\AppController;
use Illuminate\Support\ServiceProvider;

class AppControllerServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton(AppController::class, function ($app) {
            return new AppController("appfree"); //fixme
        });
    }
}
