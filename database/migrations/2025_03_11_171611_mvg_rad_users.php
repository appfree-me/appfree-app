<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::statement("ALTER TABLE mvg_rad_users MODIFY COLUMN last_pin varchar(255) default null");
        DB::statement("ALTER TABLE mvg_rad_users MODIFY COLUMN last_radnummer varchar(255) default null");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mvg_rad_users MODIFY COLUMN last_pin varchar(255) not null");
        DB::statement("ALTER TABLE mvg_rad_users MODIFY COLUMN last_radnummer varchar(255) not null");

    }
};
