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
        Schema::table('real_time_locations', function (Blueprint $table) {
            $table->string("short_name")->nullable()->default("---");
            $table->string("full_name")->nullable()->default("---");
            $table->string("datetime")->nullable()->default("---");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('real_time_locations', function (Blueprint $table) {
            //
        });
    }
};
