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
            $table->string('weekend1')->nullable();
            $table->string('weekend2')->nullable();
            $table->string('monthly_flexi_holidays')->nullable();
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
            $table->dropColumn('weekend1')->nullable();
            $table->dropColumn('weekend2')->nullable();
            $table->dropColumn('monthly_flexi_holidays')->nullable();
        });
    }
};
