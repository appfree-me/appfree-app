<?php


echo "Starting ARI Connection\n";
$ariConnector = new phpari();
echo "Active Channels: " . json_encode($ariConnector->channels()->channel_list()) . "\n";
echo "Ending ARI Connection\n";
