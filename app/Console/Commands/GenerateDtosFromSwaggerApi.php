<?php

namespace AppFree\Console\Commands;

use Illuminate\Console\Command;

class GenerateDtosFromSwaggerApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-dtos-from-swagger-api';

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

        /*
         * todo: Alle Dateien unterhalb von
         *  vendor/lelaurent/php-asterisk-swagger-api/lib/Model/Playback.php
         * durchlaufen, parsen, DtoCommands erstellen

        */
    }
}
