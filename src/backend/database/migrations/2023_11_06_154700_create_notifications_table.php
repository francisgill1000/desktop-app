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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('data')->nullable();
            $table->string('action')->nullable();
            $table->string('model')->nullable();
            $table->string('read_at')->nullable();
            $table->integer('user_id')->unsigned()->default(0);
            $table->integer('branch_id')->unsigned()->default(0);
            $table->integer('company_id')->unsigned()->default(0);
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
        Schema::dropIfExists('notifications');
    }
};
