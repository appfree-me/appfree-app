<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\AppController;
use AppFree\Ari\PhpAri;
use AppFree\MvgRad\Loader;
use AppFree\MvgRad\MyStateMachine;
use Finite\StateMachine\StateMachineInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class StateMachineInterfaceServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(StateMachineInterface::class, function (Application $app) {
            return Loader::load($app->get(AppController::class));
        });
    }
}
