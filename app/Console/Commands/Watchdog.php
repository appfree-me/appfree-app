<?php

namespace AppFree\Console\Commands;

use AppFree\appfree\modules\MvgRad\Api\Mock\MvgRadApi;
use AppFree\AppFreeCommands\AppFree\Commands\StateMachine\V1\WatchdogExecuteApiCall;
use Illuminate\Console\Command;

class Watchdog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-watchdog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Appfree watchdog background process';

    /**
     * Execute the console command.
     */
    public function handle()
    {




        // inject into appfree main instance



    }
}
