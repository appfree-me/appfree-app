<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\AppController;
use AppFree\Ari\PhpAri;
use Finite\StateMachine\StateMachineInterface;
use Illuminate\Support\ServiceProvider;

class AppControllerServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton(AppController::class, function ($app) {
            return new AppController($app->get(StateMachineInterface::class), $app->get(PhpAri::class)); //fixme
        });
    }
}
