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
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('visitor_filled_datetime')->nullable();
            $table->string('host_changed_status_datetime')->nullable();
            $table->string('guard_changed_status_datetime')->nullable();
            $table->string('checked_in_datetime')->nullable();
            $table->string('checked_out_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn('visitor_filled_datetime');
            $table->dropColumn('host_changed_status_datetime');
            $table->dropColumn('guard_changed_status_datetime');
            $table->dropColumn('checked_in_datetime');
            $table->dropColumn('checked_out_datetime');
        });
    }
};
