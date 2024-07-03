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
        Schema::create('clockings', function (Blueprint $table) {
            $table->id();
            $table->string("clock_type")->nullable();
            $table->integer("user_id")->nullable()->default(0);
            $table->string("attachment")->nullable();
            $table->integer("remarks")->nullable();
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
        Schema::dropIfExists('clockings');
    }
};
