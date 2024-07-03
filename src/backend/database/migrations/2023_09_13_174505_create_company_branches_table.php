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
        Schema::create('company_branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_code')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('created_date')->nullable();
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->string('licence_issue_by_department')->nullable();
            $table->string('licence_number')->nullable();
            $table->string('licence_expiry')->nullable();
            $table->string('telephone')->nullable();
            $table->string('po_box')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->default(null)->nullable();
            $table->integer('user_id')->default(0);
            $table->integer('company_id')->default(0);
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
        Schema::dropIfExists('company_branches');
    }
};
