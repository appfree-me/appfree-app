<?php

namespace AppFree\Console\Commands;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "watch out!\n";
    }
}
