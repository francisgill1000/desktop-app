<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('[users]', function (Blueprint $table) {
            //$table->dropColumn('enable_whatsapp_otp');

            //$table->integer('enable_whatsapp_otp')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('[users]', function (Blueprint $table) {
            //
        });
    }
};
