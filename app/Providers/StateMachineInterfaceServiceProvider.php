<?php
//declare(strict_types=1);
//
//
//namespace AppFree\Providers;
//
//use AppFree\AppController;
//use AppFree\Ari\PhpAri;
//use AppFree\MvgRad\Api\MvgRadApi;
//use AppFree\MvgRad\MvgRadStateMachineLoader;
//use AppFree\MvgRad\MvgRadArrayLoader;
//use AppFree\MvgRad\MvgRadStateMachine;
//use Finite\StateMachine\StateMachineInterface;
//use Illuminate\Contracts\Foundation\Application;
//use Illuminate\Support\ServiceProvider;
//
//class StateMachineInterfaceServiceProvider extends ServiceProvider
//{
//
//    public function register(): void
//    {
//        $this->app->singleton(StateMachineInterface::class, function (Application $app) {
//            return MvgRadStateMachineLoader::load($app->get(AppController::class), $app->get(MvgRadStateMachine::class), $app->get(MvgRadArrayLoader::class), $app->get(MvgRadApi::class));
//        });
//    }
//}
