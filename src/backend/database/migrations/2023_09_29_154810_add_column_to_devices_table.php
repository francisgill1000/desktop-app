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
        Schema::table('devices', function (Blueprint $table) {
            $table->string("function")->nullable();
            $table->string("serial_number")->nullable();
            $table->string("utc_time_zone")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * ok
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn("device_id");
            $table->dropColumn("serial_number");
            $table->dropColumn("utc_time_zone");
        });
    }
};
