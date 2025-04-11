<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mvgrad_feature_flags', function (Blueprint $table) {
            $table->string("feature");
            $table->json("json")->nullable();

        });

        DB::table('mvgrad_feature_flags')->insert([
            'feature' => 'video_dreh',
            'json' => null
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mvgrad_feature_flags');

    }
};
