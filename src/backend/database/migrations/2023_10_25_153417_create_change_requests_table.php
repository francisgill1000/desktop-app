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
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->string("request_type")->nullable();
            $table->string("from_date")->nullable();
            $table->string("to_date")->nullable();
            $table->string("remarks")->nullable();
            $table->string("status")->nullable();
            $table->string("attachment")->nullable();
            $table->integer("employee_device_id")->nullable()->default(0);
            $table->integer("branch_id")->nullable()->default(0);
            $table->integer("company_id")->nullable()->default(0);
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
        Schema::dropIfExists('change_requests');
    }
};
