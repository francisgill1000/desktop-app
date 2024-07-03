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
        Schema::create('devices_active_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');

            $table->integer('device_id');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();



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
        Schema::dropIfExists('devices_active_settings');
    }
};
