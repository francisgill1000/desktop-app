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
        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('announcements_categories', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('announcement_department', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('announcement_employee', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('assign_departments', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('assign_employees', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('auto_shifts', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('devices_active_settings', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('devices_active_weekly_settings', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('device_statuses', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });

        Schema::table('emirates_infos', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        // Schema::table('employee_leaves', function (Blueprint $table) {
        //     $table->integer("branch_id")->nullable();
        // });
        Schema::table('employee_leave_documents', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('employee_report', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('employee_timezone_mappings', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('experiences', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });

        Schema::table('holidays', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('host_companies', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });

        Schema::table('leave_count', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });

        Schema::table('leave_groups', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('leave_types', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        // Schema::table('overtimes', function (Blueprint $table) {
        //     $table->integer("branch_id")->nullable();
        // });
        Schema::table('passports', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });

        Schema::table('payroll_formulas', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('purposes', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('qualifications', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('reasons', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('shift_types', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('timezones', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('trade_licenses', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('visas', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('visitors', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('visitor_attendances', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('zones', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
        Schema::table('zone_devices', function (Blueprint $table) {
            $table->integer("branch_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_leaves', function (Blueprint $table) {
            //
        });
    }
};
