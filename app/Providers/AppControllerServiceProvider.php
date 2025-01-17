<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\AppController;
use AppFree\appfree\modules\MvgRad\MvgRadStateMachine;
use AppFree\Ari\PhpAri;
use Evenement\EventEmitter;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;

class AppControllerServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton(AppController::class, function ($app) {
            $emitter = $app->get(EventEmitter::class);

            return new AppController($emitter, $app->get(PhpAri::class), $app->get(Logger::class), $app->get(Client::class));
        });
    }
}
