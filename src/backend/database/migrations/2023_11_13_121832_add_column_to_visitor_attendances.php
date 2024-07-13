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
        Schema::table('visitor_attendances', function (Blueprint $table) {
            $table->time("in")->nullable();
            $table->time("out")->nullable();
            $table->integer("visitor_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visitor_attendances', function (Blueprint $table) {
            //
        });
    }
};
