<?php
//declare(strict_types = 1);

namespace AppFree;

pcntl_async_signals(true);

$sm = new StateMachineSample("appfree");


// setup signal handlers
pcntl_signal(SIGINT, function($signal, $info) use ($sm) {
    $sm->handler($signal, $info);
});

$sm->start();
$sm->init();
//$sm->stasisClient->open();
$sm->stasisLoop->run();
