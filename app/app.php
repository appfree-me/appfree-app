<?php

//namespace app;


//echo "Starting ARI Connection\n";
//$ariConnector = new phpari();
//echo "Active Channels: " . json_encode($ariConnector->channels()->channel_list()) . "\n";
//echo "Ending ARI Connection\n";

//function setupGlobals() {
//
//    function returnWarn()
//}
//
//
//##### global helper functions
//
//setupGlobals();
//
//#####


use AppFree\MvgRadStasisApp;

require("MvgRadApi.php");
require("MvgRadStasisAppController.php");
global $app;
$app = new MvgRadStasisApp("appfree");


pcntl_async_signals(true);

// signal handler function
function sig_handler($signo, $siginfo)
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
$res = pcntl_signal(SIGINT, "sig_handler");

$app->init();
$app->stasisClient->open();
$app->stasisLoop->run();
//$app->execute();
