<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices_active_settings', function (Blueprint $table) {
            $table->json("open_json")->nullable();
            $table->json("close_json")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices_active_settings', function (Blueprint $table) {
            $table->dropColumn('open_json');
            $table->dropColumn('close_json');
        });
    }
};
