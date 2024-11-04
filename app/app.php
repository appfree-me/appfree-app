<?php
//declare(strict_types = 1);

namespace AppFree;

pcntl_async_signals(true);

// signal handler function
function handler(int $signo, mixed $siginfo):void
{
    global $app;
    switch ($signo) {
        case SIGINT:
            // handle shutdown tasks
            echo "SIGINT caught, endHandler, closing Websocket\n";
//            $app->endHandler();
            $app->stasisClient->close();
            exit;
            break;
        default:
            // handle all other signals
    }

}

// setup signal handlers
$res = pcntl_signal(SIGINT, "handler");


$sm = new StateMachineSample("appfree");

$sm->init();
$sm->stasisClient->open();
$sm->stasisLoop->run();
