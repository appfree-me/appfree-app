<?php

namespace AppFree\Console\Commands;

use Illuminate\Console\Command;
class invokeTest {
    public function __invoke() {
        print("invoked");
    }

}
class yieldPlayground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:yield-playground';

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
        $generator = $this->direct_generator("b");
        print $generator->current();
//        $generator->send("x");
//        print $generator->current();

//        $generator->next();
//        print $generator->current();
//        print $generator->current();
//
//        $generator1 = $this->direct_generator("c");
//        print $generator1->current();
//
//        $generator->send("hi");
//        $generator1 = $generator->current();
//        var_dump($generator1);
//
//        $generator->send("hi");
//
//        print json_encode($generator->current());
//        print json_encode($generator->next());
//        print json_encode($generator->current());
//        print json_encode($generator->valid());
    }

    function direct_generator($b): \Generator
    {
//        print "-gen-";
        $a = yield "$b";

        print ("->$a");
        yield "c";
    }

    function gen2() {
        yield "super";
        yield "lokus";
    }


}
