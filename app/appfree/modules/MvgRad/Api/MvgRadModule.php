<?php

declare(strict_types=1);

namespace AppFree\appfree\modules\MvgRad\Api;

use AppFree\AppController;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Support\Facades\DB;

class MvgRadModule
{
    public static function getLastPin(string $mobilephone): string
    {
        $userId = AppController::getUserForPhonenumber($mobilephone)->id;

        try {
            return DB::table('mvgrad_transactions')->where(['user_id' => $userId, 'type' => 'rental'])->firstOrFail()->pin;
        } catch (RecordNotFoundException $e) {
            return "";
        }
    }
}
