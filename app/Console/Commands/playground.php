<?php

namespace AppFree\Console\Commands;

use Illuminate\Console\Command;

class playground extends Command
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
    private  $callable;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $callable = $this->makeCallable();
        $this->callable = $callable;
        print(is_callable($callable));
        print(is_callable($this->callable));
        $callable2 = $this->callable;
        $callable2();
    }

    private function makeCallable()
    {
        return function() {
            print "hi";
        };
    }
}
