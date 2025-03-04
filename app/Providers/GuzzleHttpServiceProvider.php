<?php

declare(strict_types=1);

namespace AppFree\Providers;

use AppFree\Ari\PhpAriConfig;
use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class GuzzleHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function (Application $app) {
            $configAsterisk = $app->get(PhpAriConfig::class);
            return new Client([
                'auth' => [$configAsterisk->asterisk_ari["username"], $configAsterisk->asterisk_ari["password"]
                    //    [$config_asterisk["host"], $config_asterisk["port"]
                ]]);
        });
    }
}
