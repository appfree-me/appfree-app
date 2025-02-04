<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\Ari\PhpAriConfig;
use Illuminate\Support\ServiceProvider;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;

class StasisClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PromiseInterface::class, function ($app) {
            $config_asterisk = $app->get(PhpAriConfig::class)->asterisk_ari;

            return \Ratchet\Client\connect($config_asterisk["transport"] . "://" .
                $config_asterisk["host"] . ":" . $config_asterisk["port"] .
                $config_asterisk["endpoint"] . "/events?api_key=" . $config_asterisk["username"] .
                ":" . $config_asterisk["password"] . "&app=" . "appfree-". config("app.env"), [], [], Loop::get()); //fixme
        });
    }
}
