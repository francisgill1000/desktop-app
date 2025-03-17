<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $data = [

            ['module' => 'dashboard', 'title' => 'access', 'name' => 'dashboard_access'],
            ['module' => 'dashboard', 'title' => 'view', 'name' => 'dashboard_view'],
            ['module' => 'dashboard', 'title' => 'create', 'name' => 'dashboard_create'],
            ['module' => 'dashboard', 'title' => 'edit', 'name' => 'dashboard_edit'],
            ['module' => 'dashboard', 'title' => 'delete', 'name' => 'dashboard_delete'],

            ['module' => 'employee', 'title' => 'access', 'name' => 'employee_access'],
            ['module' => 'employee', 'title' => 'view', 'name' => 'employee_view'],
            ['module' => 'employee', 'title' => 'create', 'name' => 'employee_create'],
            ['module' => 'employee', 'title' => 'edit', 'name' => 'employee_edit'],
            ['module' => 'employee', 'title' => 'delete', 'name' => 'employee_delete'],

            ['module' => 'employee_profile', 'title' => 'access', 'name' => 'employee_profile_access'],
            ['module' => 'employee_profile', 'title' => 'view', 'name' => 'employee_profile_view'],
            ['module' => 'employee_profile', 'title' => 'create', 'name' => 'employee_profile_create'],
            ['module' => 'employee_profile', 'title' => 'edit', 'name' => 'employee_profile_edit'],
            ['module' => 'employee_profile', 'title' => 'delete', 'name' => 'employee_profile_delete'],

            ['module' => 'employee_contact', 'title' => 'access', 'name' => 'employee_contact_access'],
            ['module' => 'employee_contact', 'title' => 'view', 'name' => 'employee_contact_view'],
            ['module' => 'employee_contact', 'title' => 'create', 'name' => 'employee_contact_create'],
            ['module' => 'employee_contact', 'title' => 'edit', 'name' => 'employee_contact_edit'],
            ['module' => 'employee_contact', 'title' => 'delete', 'name' => 'employee_contact_delete'],

            ['module' => 'employee_home_contact', 'title' => 'access', 'name' => 'employee_home_contact_access'],
            ['module' => 'employee_home_contact', 'title' => 'view', 'name' => 'employee_home_contact_view'],
            ['module' => 'employee_home_contact', 'title' => 'create', 'name' => 'employee_home_contact_create'],
            ['module' => 'employee_home_contact', 'title' => 'edit', 'name' => 'employee_home_contact_edit'],
            ['module' => 'employee_home_contact', 'title' => 'delete', 'name' => 'employee_home_contact_delete'],

            ['module' => 'employee_visa', 'title' => 'access', 'name' => 'employee_visa_access'],
            ['module' => 'employee_visa', 'title' => 'view', 'name' => 'employee_visa_view'],
            ['module' => 'employee_visa', 'title' => 'create', 'name' => 'employee_visa_create'],
            ['module' => 'employee_visa', 'title' => 'edit', 'name' => 'employee_visa_edit'],
            ['module' => 'employee_visa', 'title' => 'delete', 'name' => 'employee_visa_delete'],

            ['module' => 'employee_emirates', 'title' => 'access', 'name' => 'employee_emirates_access'],
            ['module' => 'employee_emirates', 'title' => 'view', 'name' => 'employee_emirates_view'],
            ['module' => 'employee_emirates', 'title' => 'create', 'name' => 'employee_emirates_create'],
            ['module' => 'employee_emirates', 'title' => 'edit', 'name' => 'employee_emirates_edit'],
            ['module' => 'employee_emirates', 'title' => 'delete', 'name' => 'employee_emirates_delete'],

            ['module' => 'employee_passport', 'title' => 'access', 'name' => 'employee_passport_access'],
            ['module' => 'employee_passport', 'title' => 'view', 'name' => 'employee_passport_view'],
            ['module' => 'employee_passport', 'title' => 'create', 'name' => 'employee_passport_create'],
            ['module' => 'employee_passport', 'title' => 'edit', 'name' => 'employee_passport_edit'],
            ['module' => 'employee_passport', 'title' => 'delete', 'name' => 'employee_passport_delete'],

            ['module' => 'employee_bank', 'title' => 'access', 'name' => 'employee_bank_access'],
            ['module' => 'employee_bank', 'title' => 'view', 'name' => 'employee_bank_view'],
            ['module' => 'employee_bank', 'title' => 'create', 'name' => 'employee_bank_create'],
            ['module' => 'employee_bank', 'title' => 'edit', 'name' => 'employee_bank_edit'],
            ['module' => 'employee_bank', 'title' => 'delete', 'name' => 'employee_bank_delete'],

            ['module' => 'employee_payroll', 'title' => 'access', 'name' => 'employee_payroll_access'],
            ['module' => 'employee_payroll', 'title' => 'view', 'name' => 'employee_payroll_view'],
            ['module' => 'employee_payroll', 'title' => 'create', 'name' => 'employee_payroll_create'],
            ['module' => 'employee_payroll', 'title' => 'edit', 'name' => 'employee_payroll_edit'],
            ['module' => 'employee_payroll', 'title' => 'delete', 'name' => 'employee_payroll_delete'],

            ['module' => 'employee_document', 'title' => 'access', 'name' => 'employee_document_access'],
            ['module' => 'employee_document', 'title' => 'view', 'name' => 'employee_document_view'],
            ['module' => 'employee_document', 'title' => 'create', 'name' => 'employee_document_create'],
            ['module' => 'employee_document', 'title' => 'edit', 'name' => 'employee_document_edit'],
            ['module' => 'employee_document', 'title' => 'delete', 'name' => 'employee_document_delete'],

            ['module' => 'employee_qualification', 'title' => 'access', 'name' => 'employee_qualification_access'],
            ['module' => 'employee_qualification', 'title' => 'view', 'name' => 'employee_qualification_view'],
            ['module' => 'employee_qualification', 'title' => 'create', 'name' => 'employee_qualification_create'],
            ['module' => 'employee_qualification', 'title' => 'edit', 'name' => 'employee_qualification_edit'],
            ['module' => 'employee_qualification', 'title' => 'delete', 'name' => 'employee_qualification_delete'],

            ['module' => 'employee_setting', 'title' => 'access', 'name' => 'employee_setting_access'],
            ['module' => 'employee_setting', 'title' => 'view', 'name' => 'employee_setting_view'],
            ['module' => 'employee_setting', 'title' => 'create', 'name' => 'employee_setting_create'],
            ['module' => 'employee_setting', 'title' => 'edit', 'name' => 'employee_setting_edit'],
            ['module' => 'employee_setting', 'title' => 'delete', 'name' => 'employee_setting_delete'],

            ['module' => 'employee_login', 'title' => 'access', 'name' => 'employee_login_access'],
            ['module' => 'employee_login', 'title' => 'view', 'name' => 'employee_login_view'],
            ['module' => 'employee_login', 'title' => 'create', 'name' => 'employee_login_create'],
            ['module' => 'employee_login', 'title' => 'edit', 'name' => 'employee_login_edit'],
            ['module' => 'employee_login', 'title' => 'delete', 'name' => 'employee_login_delete'],

            ['module' => 'employee_rfid', 'title' => 'access', 'name' => 'employee_rfid_access'],
            ['module' => 'employee_rfid', 'title' => 'view', 'name' => 'employee_rfid_view'],
            ['module' => 'employee_rfid', 'title' => 'create', 'name' => 'employee_rfid_create'],
            ['module' => 'employee_rfid', 'title' => 'edit', 'name' => 'employee_rfid_edit'],
            ['module' => 'employee_rfid', 'title' => 'delete', 'name' => 'employee_rfid_delete'],

            ['module' => 'leave', 'title' => 'access', 'name' => 'leave_access'],
            ['module' => 'leave', 'title' => 'view', 'name' => 'leave_view'],
            ['module' => 'leave', 'title' => 'create', 'name' => 'leave_create'],
            ['module' => 'leave', 'title' => 'edit', 'name' => 'leave_edit'],
            ['module' => 'leave', 'title' => ' delete', 'name' => 'leave_delete'],

            ['module' => 'leave_application', 'title' => 'access', 'name' => 'leave_application_access'],
            ['module' => 'leave_application', 'title' => 'view', 'name' => 'leave_application_view'],
            ['module' => 'leave_application', 'title' => 'create', 'name' => 'leave_application_create'],
            ['module' => 'leave_application', 'title' => 'edit', 'name' => 'leave_application_edit'],
            ['module' => 'leave_application', 'title' => 'delete', 'name' => 'leave_application_delete'],

            ['module' => 'announcement', 'title' => 'access', 'name' => 'announcement_access'],
            ['module' => 'announcement', 'title' => 'view', 'name' => 'announcement_view'],
            ['module' => 'announcement', 'title' => 'create', 'name' => 'announcement_create'],
            ['module' => 'announcement', 'title' => 'edit', 'name' => 'announcement_edit'],
            ['module' => 'announcement', 'title' => 'delete', 'name' => 'announcement_delete'],

            ['module' => 'announcement_category', 'title' => 'access', 'name' => 'announcement_category_access'],
            ['module' => 'announcement_category', 'title' => 'view', 'name' => 'announcement_category_view'],
            ['module' => 'announcement_category', 'title' => 'create', 'name' => 'announcement_category_create'],
            ['module' => 'announcement_category', 'title' => 'edit', 'name' => 'announcement_category_edit'],
            ['module' => 'announcement_category', 'title' => 'delete', 'name' => 'announcement_category_delete'],

            ['module' => 'employee_upload', 'title' => 'access', 'name' => 'employee_upload_access'],
            ['module' => 'employee_upload', 'title' => 'view', 'name' => 'employee_upload_view'],
            ['module' => 'employee_upload', 'title' => 'create', 'name' => 'employee_upload_create'],
            ['module' => 'employee_upload', 'title' => 'edit', 'name' => 'employee_upload_edit'],
            ['module' => 'employee_upload', 'title' => 'delete', 'name' => 'employee_upload_delete'],

            ['module' => 'shift', 'title' => 'access', 'name' => 'shift_access'],
            ['module' => 'shift', 'title' => 'view', 'name' => 'shift_view'],
            ['module' => 'shift', 'title' => 'create', 'name' => 'shift_create'],
            ['module' => 'shift', 'title' => 'edit', 'name' => 'shift_edit'],
            ['module' => 'shift', 'title' => 'delete', 'name' => 'shift_delete'],

            ['module' => 'employee_schedule', 'title' => 'access', 'name' => 'employee_schedule_access'],
            ['module' => 'employee_schedule', 'title' => 'view', 'name' => 'employee_schedule_view'],
            ['module' => 'employee_schedule', 'title' => 'create', 'name' => 'employee_schedule_create'],
            ['module' => 'employee_schedule', 'title' => 'edit', 'name' => 'employee_schedule_edit'],
            ['module' => 'employee_schedule', 'title' => 'delete', 'name' => 'employee_schedule_delete'],

            ['module' => 'device_logs', 'title' => 'access', 'name' => 'device_logs_access'],
            ['module' => 'device_logs', 'title' => 'view', 'name' => 'device_logs_view'],
            ['module' => 'device_logs', 'title' => 'create', 'name' => 'device_logs_create'],
            ['module' => 'device_logs', 'title' => 'edit', 'name' => 'device_logs_edit'],
            ['module' => 'device_logs', 'title' => 'delete', 'name' => 'device_logs_delete'],

            ['module' => 'attendance_report', 'title' => 'access', 'name' => 'attendance_report_access'],
            ['module' => 'attendance_report', 'title' => 'view', 'name' => 'attendance_report_view'],
            ['module' => 'attendance_report', 'title' => 'create', 'name' => 'attendance_report_create'],
            ['module' => 'attendance_report', 'title' => 'edit', 'name' => 'attendance_report_edit'],
            ['module' => 'attendance_report', 'title' => 'delete', 'name' => 'attendance_report_delete'],

            ['module' => 'change_request', 'title' => 'access', 'name' => 'change_request_access'],
            ['module' => 'change_request', 'title' => 'view', 'name' => 'change_request_view'],
            ['module' => 'change_request', 'title' => 'create', 'name' => 'change_request_create'],
            ['module' => 'change_request', 'title' => 'edit', 'name' => 'change_request_edit'],
            ['module' => 'change_request', 'title' => 'delete', 'name' => 'change_request_delete'],

            ['module' => 'payroll', 'title' => 'access', 'name' => 'payroll_access'],
            ['module' => 'payroll', 'title' => 'view', 'name' => 'payroll_view'],
            ['module' => 'payroll', 'title' => 'create', 'name' => 'payroll_create'],
            ['module' => 'payroll', 'title' => 'edit', 'name' => 'payroll_edit'],
            ['module' => 'payroll', 'title' => 'delete', 'name' => 'payroll_delete'],

            ['module' => 'timezone', 'title' => 'access', 'name' => 'timezone_access'],
            ['module' => 'timezone', 'title' => 'view', 'name' => 'timezone_view'],
            ['module' => 'timezone', 'title' => 'create', 'name' => 'timezone_create'],
            ['module' => 'timezone', 'title' => 'edit', 'name' => 'timezone_edit'],
            ['module' => 'timezone', 'title' => 'delete', 'name' => 'timezone_delete'],

            ['module' => 'timezone_mapping', 'title' => 'access', 'name' => 'timezone_mapping_access'],
            ['module' => 'timezone_mapping', 'title' => 'view', 'name' => 'timezone_mapping_view'],
            ['module' => 'timezone_mapping', 'title' => 'create', 'name' => 'timezone_mapping_create'],
            ['module' => 'timezone_mapping', 'title' => 'edit', 'name' => 'timezone_mapping_edit'],
            ['module' => 'timezone_mapping', 'title' => 'delete', 'name' => 'timezone_mapping_delete'],

            ['module' => 'timezone_device_mapping', 'title' => 'access', 'name' => 'employee_device_mapping_access'],
            ['module' => 'timezone_device_mapping', 'title' => 'view', 'name' => 'employee_device_mapping_view'],
            ['module' => 'timezone_device_mapping', 'title' => 'create', 'name' => 'employee_device_mapping_create'],
            ['module' => 'timezone_device_mapping', 'title' => 'edit', 'name' => 'employee_device_mapping_edit'],
            ['module' => 'timezone_device_mapping', 'title' => 'delete', 'name' => 'employee_device_mapping_delete'],

            ['module' => 'access_control_report', 'title' => 'access', 'name' => 'access_control_report_access'],
            ['module' => 'access_control_report', 'title' => 'view', 'name' => 'access_control_report_view'],
            ['module' => 'access_control_report', 'title' => 'create', 'name' => 'access_control_report_create'],
            ['module' => 'access_control_report', 'title' => 'edit', 'name' => 'access_control_report_edit'],
            ['module' => 'access_control_report', 'title' => 'delete', 'name' => 'access_control_report_delete'],

            ['module' => 'visitor_dashboard', 'title' => 'access', 'name' => 'visitor_dashboard_access'],
            ['module' => 'visitor_dashboard', 'title' => 'view', 'name' => 'visitor_dashboard_view'],
            ['module' => 'visitor_dashboard', 'title' => 'create', 'name' => 'visitor_dashboard_create'],
            ['module' => 'visitor_dashboard', 'title' => 'edit', 'name' => 'visitor_dashboard_edit'],
            ['module' => 'visitor_dashboard', 'title' => 'delete', 'name' => 'visitor_dashboard_delete'],

            ['module' => 'visitor_request', 'title' => 'access', 'name' => 'visitor_request_access'],
            ['module' => 'visitor_request', 'title' => 'view', 'name' => 'visitor_request_view'],
            ['module' => 'visitor_request', 'title' => 'create', 'name' => 'visitor_request_create'],
            ['module' => 'visitor_request', 'title' => 'edit', 'name' => 'visitor_request_edit'],
            ['module' => 'visitor_request', 'title' => 'delete', 'name' => 'visitor_request_delete'],

            ['module' => 'visitor', 'title' => 'access', 'name' => 'visitor_access'],
            ['module' => 'visitor', 'title' => 'view', 'name' => 'visitor_view'],
            ['module' => 'visitor', 'title' => 'create', 'name' => 'visitor_create'],
            ['module' => 'visitor', 'title' => 'edit', 'name' => 'visitor_edit'],
            ['module' => 'visitor', 'title' => 'delete', 'name' => 'visitor_delete'],

            ['module' => 'host', 'title' => 'access', 'name' => 'host_access'],
            ['module' => 'host', 'title' => 'view', 'name' => 'host_view'],
            ['module' => 'host', 'title' => 'create', 'name' => 'host_create'],
            ['module' => 'host', 'title' => 'edit', 'name' => 'host_edit'],
            ['module' => 'host', 'title' => 'delete', 'name' => 'host_delete'],

            ['module' => 'purpose', 'title' => 'access', 'name' => 'purpose_access'],
            ['module' => 'purpose', 'title' => 'view', 'name' => 'purpose_view'],
            ['module' => 'purpose', 'title' => 'create', 'name' => 'purpose_create'],
            ['module' => 'purpose', 'title' => 'edit', 'name' => 'purpose_edit'],
            ['module' => 'purpose', 'title' => 'delete', 'name' => 'purpose_delete'],

            ['module' => 'visitor_logs', 'title' => 'access', 'name' => 'visitor_logs_access'],
            ['module' => 'visitor_logs', 'title' => 'view', 'name' => 'visitor_logs_view'],
            ['module' => 'visitor_logs', 'title' => 'create', 'name' => 'visitor_logs_create'],
            ['module' => 'visitor_logs', 'title' => 'edit', 'name' => 'visitor_logs_edit'],
            ['module' => 'visitor_logs', 'title' => 'delete', 'name' => 'visitor_logs_delete'],

            ['module' => 'zone', 'title' => 'access', 'name' => 'zone_access'],
            ['module' => 'zone', 'title' => 'view', 'name' => 'zone_view'],
            ['module' => 'zone', 'title' => 'create', 'name' => 'zone_create'],
            ['module' => 'zone', 'title' => 'edit', 'name' => 'zone_edit'],
            ['module' => 'zone', 'title' => 'delete', 'name' => 'zone_delete'],

            ['module' => 'visitor_reports', 'title' => 'access', 'name' => 'visitor_reports_access'],
            ['module' => 'visitor_reports', 'title' => 'view', 'name' => 'visitor_reports_view'],
            ['module' => 'visitor_reports', 'title' => 'create', 'name' => 'visitor_reports_create'],
            ['module' => 'visitor_reports', 'title' => 'edit', 'name' => 'visitor_reports_edit'],
            ['module' => 'visitor_reports', 'title' => 'delete', 'name' => 'visitor_reports_delete'],

            ['module' => 'unknown', 'title' => 'access', 'name' => 'unknown_access'],
            ['module' => 'unknown', 'title' => 'view', 'name' => 'unknown_view'],
            ['module' => 'unknown', 'title' => 'create', 'name' => 'unknown_create'],
            ['module' => 'unknown', 'title' => 'edit', 'name' => 'unknown_edit'],
            ['module' => 'unknown', 'title' => 'delete', 'name' => 'unknown_delete'],

            ['module' => 'performance_report', 'title' => 'access', 'name' => 'performance_report_access'],
            ['module' => 'performance_report', 'title' => 'view', 'name' => 'performance_report_view'],
            ['module' => 'performance_report', 'title' => 'create', 'name' => 'performance_report_create'],
            ['module' => 'performance_report', 'title' => 'edit', 'name' => 'performance_report_edit'],
            ['module' => 'performance_report', 'title' => 'delete', 'name' => 'performance_report_delete'],

            ['module' => 'summary_report', 'title' => 'access', 'name' => 'summary_report_access'],
            ['module' => 'summary_report', 'title' => 'view', 'name' => 'summary_report_view'],
            ['module' => 'summary_report', 'title' => 'create', 'name' => 'summary_report_create'],
            ['module' => 'summary_report', 'title' => 'edit', 'name' => 'summary_report_edit'],
            ['module' => 'summary_report', 'title' => 'delete', 'name' => 'summary_report_delete'],

            ['module' => 'document_expiry', 'title' => 'Web Logs access', 'name' => 'document_expiry_access'],
            ['module' => 'document_expiry', 'title' => 'Web Logs view', 'name' => 'document_expiry_view'],
            ['module' => 'document_expiry', 'title' => 'Web Logs create', 'name' => 'document_expiry_create'],
            ['module' => 'document_expiry', 'title' => 'Web Logs edit', 'name' => 'document_expiry_edit'],
            ['module' => 'document_expiry', 'title' => 'Web Logs delete', 'name' => 'document_expiry_delete'],

            ['module' => 'web_login_logs', 'title' => 'Web Logs access', 'name' => 'web_login_logs_access'],
            ['module' => 'web_login_logs', 'title' => 'Web Logs view', 'name' => 'web_login_logs_view'],
            ['module' => 'web_login_logs', 'title' => 'Web Logs create', 'name' => 'web_login_logs_create'],
            ['module' => 'web_login_logs', 'title' => 'Web Logs edit', 'name' => 'web_login_logs_edit'],
            ['module' => 'web_login_logs', 'title' => 'Web Logs delete', 'name' => 'web_login_logs_delete'],

            ['module' => 'company_profile', 'title' => 'access', 'name' => 'company_profile_access'],
            ['module' => 'company_profile', 'title' => 'view', 'name' => 'company_profile_view'],
            ['module' => 'company_profile', 'title' => 'create', 'name' => 'company_profile_create'],
            ['module' => 'company_profile', 'title' => 'edit', 'name' => 'company_profile_edit'],
            ['module' => 'company_profile', 'title' => 'delete', 'name' => 'company_profile_delete'],

            ['module' => 'license', 'title' => 'access', 'name' => 'license_access'],
            ['module' => 'license', 'title' => 'view', 'name' => 'license_view'],
            ['module' => 'license', 'title' => 'create', 'name' => 'license_create'],
            ['module' => 'license', 'title' => 'edit', 'name' => 'license_edit'],
            ['module' => 'license', 'title' => 'delete', 'name' => 'license_delete'],

            ['module' => 'document', 'title' => 'access', 'name' => 'document_access'],
            ['module' => 'document', 'title' => 'view', 'name' => 'document_view'],
            ['module' => 'document', 'title' => 'create', 'name' => 'document_create'],
            ['module' => 'document', 'title' => 'edit', 'name' => 'document_edit'],
            ['module' => 'document', 'title' => 'delete', 'name' => 'document_delete'],

            ['module' => 'password', 'title' => 'access', 'name' => 'password_access'],
            ['module' => 'password', 'title' => 'view', 'name' => 'password_view'],
            ['module' => 'password', 'title' => 'create', 'name' => 'password_create'],
            ['module' => 'password', 'title' => 'edit', 'name' => 'password_edit'],
            ['module' => 'password', 'title' => 'delete', 'name' => 'password_delete'],

            ['module' => 'admin', 'title' => 'access', 'name' => 'admin_access'],
            ['module' => 'admin', 'title' => 'view', 'name' => 'admin_view'],
            ['module' => 'admin', 'title' => 'create', 'name' => 'admin_create'],
            ['module' => 'admin', 'title' => 'edit', 'name' => 'admin_edit'],
            ['module' => 'admin', 'title' => 'delete', 'name' => 'admin_delete'],

            ['module' => 'whatsapp', 'title' => 'access', 'name' => 'whatsapp_access'],
            ['module' => 'whatsapp', 'title' => 'view', 'name' => 'whatsapp_view'],
            ['module' => 'whatsapp', 'title' => 'create', 'name' => 'whatsapp_create'],
            ['module' => 'whatsapp', 'title' => 'edit', 'name' => 'whatsapp_edit'],
            ['module' => 'whatsapp', 'title' => 'delete', 'name' => 'whatsapp_delete'],

            ['module' => 'performance_rating_description', 'title' => 'access', 'name' => 'performance_rating_description_access'],
            ['module' => 'performance_rating_description', 'title' => 'view', 'name' => 'performance_rating_description_view'],
            ['module' => 'performance_rating_description', 'title' => 'create', 'name' => 'performance_rating_description_create'],
            ['module' => 'performance_rating_description', 'title' => 'edit', 'name' => 'performance_rating_description_edit'],
            ['module' => 'performance_rating_description', 'title' => 'delete', 'name' => 'performance_rating_description_delete'],

            ['module' => 'attendance_rating', 'title' => 'access', 'name' => 'attendance_rating_access'],
            ['module' => 'attendance_rating', 'title' => 'view', 'name' => 'attendance_rating_view'],
            ['module' => 'attendance_rating', 'title' => 'create', 'name' => 'attendance_rating_create'],
            ['module' => 'attendance_rating', 'title' => 'edit', 'name' => 'attendance_rating_edit'],
            ['module' => 'attendance_rating', 'title' => 'delete', 'name' => 'attendance_rating_delete'],

            ['module' => 'branch', 'title' => 'access', 'name' => 'branch_access'],
            ['module' => 'branch', 'title' => 'view', 'name' => 'branch_view'],
            ['module' => 'branch', 'title' => 'create', 'name' => 'branch_create'],
            ['module' => 'branch', 'title' => 'edit', 'name' => 'branch_edit'],
            ['module' => 'branch', 'title' => 'delete', 'name' => 'branch_delete'],

            ['module' => 'department', 'title' => 'access', 'name' => 'department_access'],
            ['module' => 'department', 'title' => 'view', 'name' => 'department_view'],
            ['module' => 'department', 'title' => 'create', 'name' => 'department_create'],
            ['module' => 'department', 'title' => 'edit', 'name' => 'department_edit'],
            ['module' => 'department', 'title' => 'delete', 'name' => 'department_delete'],

            ['module' => 'sub_department', 'title' => 'access', 'name' => 'sub_department_access'],
            ['module' => 'sub_department', 'title' => 'view', 'name' => 'sub_department_view'],
            ['module' => 'sub_department', 'title' => 'create', 'name' => 'sub_department_create'],
            ['module' => 'sub_department', 'title' => 'edit', 'name' => 'sub_department_edit'],
            ['module' => 'sub_department', 'title' => 'delete', 'name' => 'sub_department_delete'],

            ['module' => 'designation', 'title' => 'access', 'name' => 'designation_access'],
            ['module' => 'designation', 'title' => 'view', 'name' => 'designation_view'],
            ['module' => 'designation', 'title' => 'create', 'name' => 'designation_create'],
            ['module' => 'designation', 'title' => 'edit', 'name' => 'designation_edit'],
            ['module' => 'designation', 'title' => 'delete', 'name' => 'designation_delete'],

            ['module' => 'automation_absent', 'title' => 'access', 'name' => 'automation_absent_access'],
            ['module' => 'automation_absent', 'title' => 'view', 'name' => 'automation_absent_view'],
            ['module' => 'automation_absent', 'title' => 'create', 'name' => 'automation_absent_create'],
            ['module' => 'automation_absent', 'title' => 'edit', 'name' => 'automation_absent_edit'],
            ['module' => 'automation_absent', 'title' => 'delete', 'name' => 'automation_absent_delete'],

            ['module' => 'automation_attendance', 'title' => 'access', 'name' => 'automation_attendance_access'],
            ['module' => 'automation_attendance', 'title' => 'view', 'name' => 'automation_attendance_view'],
            ['module' => 'automation_attendance', 'title' => 'create', 'name' => 'automation_attendance_create'],
            ['module' => 'automation_attendance', 'title' => 'edit', 'name' => 'automation_attendance_edit'],
            ['module' => 'automation_attendance', 'title' => 'delete', 'name' => 'automation_attendance_delete'],

            ['module' => 'automation_device', 'title' => 'access', 'name' => 'automation_device_access'],
            ['module' => 'automation_device', 'title' => 'view', 'name' => 'automation_device_view'],
            ['module' => 'automation_device', 'title' => 'create', 'name' => 'automation_device_create'],
            ['module' => 'automation_device', 'title' => 'edit', 'name' => 'automation_device_edit'],
            ['module' => 'automation_device', 'title' => 'delete', 'name' => 'automation_device_delete'],

            ['module' => 'automation_document', 'title' => 'access', 'name' => 'automation_document_access'],
            ['module' => 'automation_document', 'title' => 'view', 'name' => 'automation_document_view'],
            ['module' => 'automation_document', 'title' => 'create', 'name' => 'automation_document_create'],
            ['module' => 'automation_document', 'title' => 'edit', 'name' => 'automation_document_edit'],
            ['module' => 'automation_document', 'title' => 'delete', 'name' => 'automation_document_delete'],

            ['module' => 'automation_access_control', 'title' => 'access', 'name' => 'automation_access_control_access'],
            ['module' => 'automation_access_control', 'title' => 'view', 'name' => 'automation_access_control_view'],
            ['module' => 'automation_access_control', 'title' => 'create', 'name' => 'automation_access_control_create'],
            ['module' => 'automation_access_control', 'title' => 'edit', 'name' => 'automation_access_control_edit'],
            ['module' => 'automation_access_control', 'title' => 'delete', 'name' => 'automation_access_control_delete'],

            ['module' => 'role', 'title' => 'access', 'name' => 'role_access'],
            ['module' => 'role', 'title' => 'view', 'name' => 'role_view'],
            ['module' => 'role', 'title' => 'create', 'name' => 'role_create'],
            ['module' => 'role', 'title' => 'edit', 'name' => 'role_edit'],
            ['module' => 'role', 'title' => 'delete', 'name' => 'role_delete'],

            ['module' => 'device', 'title' => 'access', 'name' => 'device_access'],
            ['module' => 'device', 'title' => 'view', 'name' => 'device_view'],
            ['module' => 'device', 'title' => 'create', 'name' => 'device_create'],
            ['module' => 'device', 'title' => 'edit', 'name' => 'device_edit'],
            ['module' => 'device', 'title' => 'delete', 'name' => 'device_delete'],

            ['module' => 'holiday', 'title' => 'access', 'name' => 'holiday_access'],
            ['module' => 'holiday', 'title' => 'view', 'name' => 'holiday_view'],
            ['module' => 'holiday', 'title' => 'create', 'name' => 'holiday_create'],
            ['module' => 'holiday', 'title' => 'edit', 'name' => 'holiday_edit'],
            ['module' => 'holiday', 'title' => 'delete', 'name' => 'holiday_delete'],

            ['module' => 'leave_type', 'title' => 'access', 'name' => 'leave_type_access'],
            ['module' => 'leave_type', 'title' => 'view', 'name' => 'leave_type_view'],
            ['module' => 'leave_type', 'title' => 'create', 'name' => 'leave_type_create'],
            ['module' => 'leave_type', 'title' => 'edit', 'name' => 'leave_type_edit'],
            ['module' => 'leave_type', 'title' => 'delete', 'name' => 'leave_type_delete'],

            ['module' => 'leave_group', 'title' => 'access', 'name' => 'leave_group_access'],
            ['module' => 'leave_group', 'title' => 'view', 'name' => 'leave_group_view'],
            ['module' => 'leave_group', 'title' => 'create', 'name' => 'leave_group_create'],
            ['module' => 'leave_group', 'title' => 'edit', 'name' => 'leave_group_edit'],
            ['module' => 'leave_group', 'title' => 'delete', 'name' => 'leave_group_delete'],

            ['module' => 'payroll_formula', 'title' => 'access', 'name' => 'payroll_formula_access'],
            ['module' => 'payroll_formula', 'title' => 'view', 'name' => 'payroll_formula_view'],
            ['module' => 'payroll_formula', 'title' => 'create', 'name' => 'payroll_formula_create'],
            ['module' => 'payroll_formula', 'title' => 'edit', 'name' => 'payroll_formula_edit'],
            ['module' => 'payroll_formula', 'title' => 'delete', 'name' => 'payroll_formula_delete'],

            ['module' => 'payroll_generation', 'title' => 'access', 'name' => 'payroll_generation_date_access'],
            ['module' => 'payroll_generation', 'title' => 'view', 'name' => 'payroll_generation_date_view'],
            ['module' => 'payroll_generation', 'title' => 'create   ', 'name' => 'payroll_generation_date_create'],
            ['module' => 'payroll_generation', 'title' => 'edit', 'name' => 'payroll_generation_date_edit'],
            ['module' => 'payroll_generation', 'title' => 'Delete', 'name' => 'payroll_generation_date_delete'],

            ['module' => 'automation_mail_content', 'title' => 'access', 'name' => 'automation_mail_content_access'],
            ['module' => 'automation_mail_content', 'title' => 'view  ', 'name' => 'automation_mail_content_view'],
            ['module' => 'automation_mail_content', 'title' => 'create', 'name' => 'automation_mail_content_create'],
            ['module' => 'automation_mail_content', 'title' => 'edit  ', 'name' => 'automation_mail_content_edit'],
            ['module' => 'automation_mail_content', 'title' => 'Delete', 'name' => 'automation_mail_content_delete'],
        ];

        // Permission::truncate();
        // Permission::insert($data);
        // echo Permission::count();

        foreach ($data as $key => $dataArray) {
            $result = Permission::updateOrCreate(['name' => $dataArray['name']], $dataArray);
            // echo json_encode($result) . "\n";
        }

        echo "<pre>";

        ld(Permission::pluck("name"));

        // run this command to seed the data => php artisan db:seed --class=PermissionSeeder
    }
}
