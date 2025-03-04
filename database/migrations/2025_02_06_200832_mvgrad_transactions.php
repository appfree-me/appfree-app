<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mvgrad_transactions', function (Blueprint $table) {
            $table->string('api_id', 15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mvgrad_transactions', function (Blueprint $table) {
            $table->dropColumn('api_id');
        });
    }
};
