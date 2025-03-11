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

        Schema::table('mvg_rad_apis', function (Blueprint $table) {
            $table->string("user_agent");
            $table->string("api_session");
        });
        DB::statement("ALTER TABLE mvg_rad_apis MODIFY COLUMN group_id varchar(36) default null");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mvg_rad_apis', function (Blueprint $table) {
            $table->dropColumn('user_agent');
            $table->dropColumn('api_session');
        });
        DB::statement("ALTER TABLE mvg_rad_apis MODIFY COLUMN group_id varchar(36) not null");

    }
};
