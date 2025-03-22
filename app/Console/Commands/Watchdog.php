<?php

namespace AppFree\Console\Commands;

use AppFree\AppFreeCommands\AppFree\Commands\StateMachine\V1\WatchdogExecuteApiCall;
use AppFree\Models\WatchdogLog;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Monolog\Logger;

class Watchdog extends Command
{
    public const CHECK_INTERVAL_SECONDS = 5 * 60;
    public const int LATE_PONG_THRESHOLD_SECONDS = 2;
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

    public function __construct(private Logger $logger)
    {
        parent::__construct();
    }

    private function parseSystemctlShowOutput(string $lines): array
    {
        $result = [];

        foreach (explode("\n", $lines) as $line) {
            if ($line === "") {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            if (strlen($key)) {
                $result[$key] = trim($value);
            }
        }

        return $result;
    }

    private function systemctlShowCommand(string $serviceName)
    {
        return "systemctl --user show --no-pager '$serviceName'";
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function handle(): void
    {
        $lastId = 0;

        while (sleep(self::CHECK_INTERVAL_SECONDS) === 0) {
            list($results, $lastId) = $this->checkForViolations($lastId);
            foreach ($results["violations"] as $name => $count) {
                $this->logger->info("Watchdog detected violation: $name, count: $count");
            }
        }
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function atLeastCheckIntervalAgo(string $datestr): bool
    {
        $dateTime = (new DateTime($datestr))->getTimestamp();
        $now = (new DateTime('now'))->getTimestamp();

        return ($now - $dateTime > self::CHECK_INTERVAL_SECONDS);
    }

    /**
     * Regularly check if the required DB entries are being created
     * @throws \DateMalformedStringException
     */
    public function checkForViolations(int $lastId = 0): array
    {
        $results = ['rows_total' => 0, 'violations' => []];

        // Precondition: appfree-app_{APP_ENV} has been running for more than 5 minutes
        // (Does not check if unit dies all the time e. g. because it can't connect to asterisk)
        $output = $this->parseSystemctlShowOutput(shell_exec($this->systemctlShowCommand(config('service.name'))));
        if (!($output["ActiveState"] === "active" && $this->atLeastCheckIntervalAgo($output["ActiveEnterTimestamp"]))) {
            $results["violations"]["service_inactive"] = true;
            return $results;
        }


        // Check if required DB entries are being created
        // Last DB entry should not be older than 2*WATCHDOG_PING_INTERVAL

        $records = DB::select(
            'SELECT * FROM watchdog_logs WHERE unix_timestamp(now()) - unix_timestamp(created_at) <= ? and id > ? order by id',
            [self::CHECK_INTERVAL_SECONDS, $lastId]
        );

        $recentLogEntries = WatchdogLog::hydrate($records);

        $results['rows_total'] = count($recentLogEntries);

        if (count($recentLogEntries) === 0) {
            $results["violations"]["no_entities_created"] = true;

        } else {
            $lastId = $recentLogEntries[count($recentLogEntries) - 1]->id;
        }

        // Check that enough entries received a Pong
        foreach ($recentLogEntries as $recentLogEntry) {
            if ($recentLogEntry->seconds_to_processing === null) {
                $results["violations"]["missing_pong"] = ($results["violations"]["missing_pong"] ?? 0) + 1;
            }
        }

        // Check that Pong delay is not too big
        foreach ($recentLogEntries as $recentLogEntry) {
            if ($recentLogEntry->seconds_to_processing > self::LATE_PONG_THRESHOLD_SECONDS) {
                $results["violations"]["late_pong"] = ($results["violations"]["late_pong"] ?? 0) + 1;
            }
        }

        return [$results, $lastId];
    }
}
