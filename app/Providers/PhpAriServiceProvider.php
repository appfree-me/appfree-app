<?php
declare(strict_types=1);


namespace AppFree\Providers;


use AppFree\AppController;
use AppFree\Ari\PhpAri;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class PhpAriServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(PhpAri::class, function (Application $app) {

            return new PhpAri("appfree", $app->get(AppController::class)); //fixme
//            return new Connection(config('riak'));
        });
    }
}
