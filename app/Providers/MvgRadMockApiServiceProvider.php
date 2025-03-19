<?php

declare(strict_types=1);

namespace AppFree\Providers;

use AppFree\appfree\modules\MvgRad\Api\Mock\MvgRadApi as MvgRadApiMock;
use AppFree\appfree\modules\MvgRad\MvgRadStateMachine;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MvgRadMockApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MvgRadApiMock::class, function (Application $app) {
            // fixme only works for watchdog right now b/c class is
            // not fully initialized here
            return new MvgRadApiMock(new MvgRadStateMachine());
        });
    }
}
