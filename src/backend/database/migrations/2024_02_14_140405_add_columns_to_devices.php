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
            $table->integer("temparature_alarm_status")->default(0);
            $table->dateTime("temparature_alarm_start_datetime")->nullable();
            $table->dateTime("temparature_alarm_end_datetime")->nullable();

            $table->integer("humidity_alarm_status")->default(0);
            $table->dateTime("humidity_alarm_start_datetime")->nullable();
            $table->dateTime("humidity_alarm_end_datetime")->nullable();

            $table->integer("fire_alarm_status")->default(0);
            $table->dateTime("fire_alarm_start_datetime")->nullable();
            $table->dateTime("fire_alarm_end_datetime")->nullable();

            $table->integer("water_alarm_status")->default(0);
            $table->dateTime("water_alarm_start_datetime")->nullable();
            $table->dateTime("water_alarm_end_datetime")->nullable();

            $table->integer("power_alarm_status")->default(0);
            $table->dateTime("power_alarm_start_datetime")->nullable();
            $table->dateTime("power_alarm_end_datetime")->nullable();

            $table->integer("door_open_status")->default(0);
            $table->dateTime("door_open_start_datetime")->nullable();
            $table->dateTime("door_open_end_datetime")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            //
        });
    }
};
