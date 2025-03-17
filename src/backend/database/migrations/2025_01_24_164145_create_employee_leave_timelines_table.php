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
        Schema::create('employee_leave_timelines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_leave_id')->default(0); // Use unsignedBigInteger for foreign keys
            $table->string('description')->nullable();
            $table->timestamps();
        
            // Add the foreign key constraint with cascading delete
            $table->foreign('employee_leave_id')
                  ->references('id')
                  ->on('employee_leaves')
                  ->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_leave_timelines');
    }
};
