<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->insert([
            'name' => 'dreh',
            'email' => 'dreh@example.com',
            'password' => 'aaa',
            'mobilephone' => 'videodreh'
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('mobilephone', 'videodreh')->delete();
    }
};
