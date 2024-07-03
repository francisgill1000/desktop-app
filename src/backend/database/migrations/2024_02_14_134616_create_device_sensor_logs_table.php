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
        Schema::create('device_sensor_logs', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id")->nullable();
            $table->integer("banch_id")->nullable();
            $table->string("serial_number")->nullable();
            $table->dateTime("log_time")->nullable();
            $table->integer("temparature")->nullable();
            $table->integer("humidity")->nullable();
            $table->integer("water_leakage")->default(0);
            $table->integer("power_failure")->default(0);
            $table->integer("door_status")->default(0);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_sensor_logs');
    }
};
