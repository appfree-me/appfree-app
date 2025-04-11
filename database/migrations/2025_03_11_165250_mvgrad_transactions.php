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
        Schema::table('mvgrad_transactions', function ($table) {
            $table->string('mvg_id');
            $table->string('mvg_start_date');
        });
        DB::statement("ALTER TABLE mvgrad_transactions MODIFY COLUMN mvg_id varchar(255) default null");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropColumns("mvgrad_transactions", ["mvg_id", "mvg_start_date"]);
    }
};
