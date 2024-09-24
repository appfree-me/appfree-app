<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/asterisk', [\App\Http\Controllers\AsteriskController::class, 'index']);

