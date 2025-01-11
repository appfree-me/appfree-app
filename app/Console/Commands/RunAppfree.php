<?php

namespace AppFree\Console\Commands;

use AppFree\AppController;
use AppFree\AppFree;
use AppFree\Ari\PhpAri;
use Illuminate\Console\Command;

class RunAppfree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-appfree';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to Asterisk ARI via Websocket and execute configured Appfree Modules';

    /**
     * Execute the console command.
     */
    public function handle(AppController $app): void
    {
        AppFree::app($app);
    }
}
