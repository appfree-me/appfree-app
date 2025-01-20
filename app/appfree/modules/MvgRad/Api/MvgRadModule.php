<?php

namespace AppFree\appfree\modules\MvgRad\Api;

use AppFree\AppController;
use Illuminate\Support\Facades\DB;
use React\Dns\RecordNotFoundException;

class MvgRadModule
{
    public static function getLastPin(string $mobilephone): string
    {
        $userId = AppController::getUserId($mobilephone);

        try {
            return DB::table('mvgrad_transactions')->where(['user_id' => $userId, 'type' => 'rental'])->firstOrFail()->pin;
        } catch (RecordNotFoundException $e) {
            return "";
        }
    }
}
