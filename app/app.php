<?php

//namespace app;


//echo "Starting ARI Connection\n";
//$ariConnector = new phpari();
//echo "Active Channels: " . json_encode($ariConnector->channels()->channel_list()) . "\n";
//echo "Ending ARI Connection\n";

require("MvgRadApi.php");
require("MvgRadStasisApp.php");
$app = new MvgRadStasisApp("appfree");

$app->execute();
