<?php

namespace AppFree\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Monolog\Logger;

class RunCleaning extends Command
{
    public const array CLEANING_TABLES = [
        # table name    => SQL WHERE clause
        'watchdog_logs' => 'created_at < NOW() - INTERVAL 60 DAY',
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-cleaning';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean cleaned tables';

    /**
     * Execute the console command.
     */
    public function handle(Logger $logger): void
    {
        foreach (self::CLEANING_TABLES as $table => $whereClause) {
            $logger->notice("Cleaning table {$table} where {$whereClause}");

            DB::Statement("DELETE FROM $table WHERE $whereClause");
        }
    }
}
