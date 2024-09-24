<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\InputOption;

class CallRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:call-route {uri}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call a Laravel route';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $request = Request::create($this->arguments()['uri'], 'GET');
        $this->info(app()['Illuminate\Contracts\Http\Kernel']->handle($request));
    }


}
