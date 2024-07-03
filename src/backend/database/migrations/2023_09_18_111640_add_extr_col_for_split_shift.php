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
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('on_duty_time1')->default("---");
            $table->string('off_duty_time1')->default("---");
            $table->string('beginning_in1')->default("---");
            $table->string('beginning_out1')->default("---");
            $table->string('ending_in1')->default("---");
            $table->string('ending_out1')->default("---");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {

            $table->dropColumn('on_duty_time1');
            $table->dropColumn('off_duty_time1');
            $table->dropColumn('beginning_in1');
            $table->dropColumn('beginning_out1');
            $table->dropColumn('ending_in1');
            $table->dropColumn('ending_out1');
        });
    }
};
