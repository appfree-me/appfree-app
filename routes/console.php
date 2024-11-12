<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('start-ari-client', function () {

    $this->comment(Inspiring::quote());
})->purpose('Connect to Asterisk ARI via Websocket and run configured appfree modules');
