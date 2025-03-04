<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mvgrad_transactions', function ($table) {
            $table->string('group_id', 36)->before('type');
        });
        DB::statement("ALTER TABLE mvgrad_transactions MODIFY COLUMN type ENUM('rental', 'return') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mvgrad_transactions MODIFY COLUMN type ENUM('rental')");
        Schema::dropColumns("mvgrad_transactions", "group_id");
        //
    }
};
