<?php

declare(strict_types=1);

namespace AppFree\Console\Commands;

use Illuminate\Console\Command;

/**
 * Suppress all rules containing "unused" in this
 * class
 *
 * @SuppressWarnings("PHPMD")
 */
class Playground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:playground';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private $callable;

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $a = null;
        $b = null;
        $c = "c";
        $x = $a ?? $b ?? 4;
        var_dump($x);
    }

    private function makeCallable()
    {
        return function () {
            print "hi";
        };
    }
}
