<?php
declare(strict_types=1);


namespace AppFree\Providers;

use AppFree\Ari\PhpAriConfig;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Logger::class, function (Application $app) {
            $config_asterisk = $app->get(PhpAriConfig::class);

            $logger = new \Monolog\Logger("appfree-" . config("app.env") );

            if ($config_asterisk->general['logfile'] == "console") {
                $logWriter = new StreamHandler("php://stdout");
            } else {
                $logWriter = new StreamHandler($config_asterisk->general['logfile']);
            }

            $logger->pushHandler($logWriter);

            return $logger;
        });
    }
}
