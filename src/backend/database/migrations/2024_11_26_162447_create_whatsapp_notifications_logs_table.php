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
        Schema::create('whatsapp_notifications_logs', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("whatsapp_number");
            $table->string("message");
            $table->boolean("sent_status")->default(false);
            $table->dateTime("status_datetime")->nullable();
            $table->integer("retry_count")->default(0);



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
        Schema::dropIfExists('whatsapp_notifications_logs');
    }
};
