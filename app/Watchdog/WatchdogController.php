<?php

declare(strict_types=1);

namespace AppFree\Watchdog;

use AppFree\Ari\PhpAri;
use Evenement\EventEmitterInterface;
use GuzzleHttp\Client;
use Monolog\Logger;

class WatchdogController
{
    public function __construct(private EventEmitterInterface $emitter, private PhpAri $phpAri, private Logger $stasisLogger, private Client $client)
    {
    }




}
