<?php

return [

    // Enable internal generation / saving of monitoring events?
    'internal' => env('WATCHDOG_MONITORING', false),

    // Enable external monitoring of the generated events?
    'external' => env('WATCHDOG_MONITORING', false),

    'ping-interval' => env('WATCHDOG_PING_INTERVAL', 60),
    'check-interval' => env('WATCHDOG_CHECK_INTERVAL', 5 * env('WATCHDOG_PING_INTERVAL', 60)),
];
