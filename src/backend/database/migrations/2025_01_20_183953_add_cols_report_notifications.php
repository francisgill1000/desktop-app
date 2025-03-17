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
        Schema::table('report_notifications', function (Blueprint $table) {
            $table->string("from_time")->nullable();
            $table->string("to_time")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_notifications', function (Blueprint $table) {
            $table->dropColumn("from_time");
            $table->dropColumn("to_time");
        });
    }
};
