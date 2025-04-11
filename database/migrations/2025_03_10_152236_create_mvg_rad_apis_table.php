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
        Schema::create('mvg_rad_apis', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("api_key");
            $table->string("api_client_os");
            $table->string("api_client_os_version");
            $table->string("api_client_version");
            $table->string("api_client_device");
            $table->string("api_build_info");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mvg_rad_apis');
    }
};
