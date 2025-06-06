<?php

declare(strict_types=1);

namespace AppFree\Providers;

use App\ReactWebsocketInterface;
use AppFree\Ari\PhpAriConfig;
use AppFree\Constants\App;
use Illuminate\Support\ServiceProvider;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;

class PromiseInterfaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReactWebsocketInterface::class, function ($app) {
            $configAsterisk = $app->get(PhpAriConfig::class)->asterisk_ari;
            //fixme parameterized urlencode
            $loop = Loop::get();
            $name = App::name();
            return \Ratchet\Client\connect($configAsterisk["transport"] . "://" .
                $configAsterisk["host"] . ":" . $configAsterisk["port"] .
                $configAsterisk["endpoint"] . "/events?api_key=" . $configAsterisk["username"] .
                ":" . $configAsterisk["password"] . "&app=" . $name, [], [], $loop); //fixme
        });
    }
}
