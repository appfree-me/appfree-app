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
        Schema::create('mvgrad_mock_state', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('phone', 36);
            $table->enum('rental_state', ['running', 'not-running']);
            $table->string('pin', 10)->nullable();
            $table->string('radnummer', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mvgrad_mock_state');
    }
};
