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
        Schema::table('tanents', function (Blueprint $table) {
            $table->string("whatsapp_number")->nullable();
            $table->string("date_of_birth")->nullable();
            $table->string("nationality")->nullable();
            $table->string("car_number")->nullable();
            $table->string("parking_number")->nullable();
            $table->string("web_access")->nullable();
            $table->string("rfid")->nullable();
            $table->string("pin")->nullable();
            $table->string("address")->nullable();
            $table->string("passport_doc")->nullable();
            $table->string("id_doc")->nullable();
            $table->string("contract_doc")->nullable();
            $table->string("ejari_doc")->nullable();
            $table->string("license_doc")->nullable();
            $table->string("others_doc")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tanents', function (Blueprint $table) {
            //
        });
    }
};
