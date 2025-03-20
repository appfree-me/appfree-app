<?php

return [

    // Enable internal generation / saving of monitoring events?
    'watchdog-internal' => env('WATCHDOG_MONITORING', 'false'),

    // Enable external monitoring of the generated events?
    'watchdog-external' => env('WATCHDOG_MONITORING', 'false'),

];
