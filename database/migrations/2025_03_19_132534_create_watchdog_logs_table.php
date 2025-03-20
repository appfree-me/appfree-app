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
        Schema::create('watchdog_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->binary('unique_id', 16)->unique();
            $table->unsignedBigInteger('nanoseconds_created_at');
            $table->double('seconds_to_processing')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchdog_logs');
    }
};
