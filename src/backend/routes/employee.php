<?php

use App\Http\Controllers\Dashboards\EmployeeDashboard;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

Route::get('/employee-statistics', [EmployeeDashboard::class, 'statistics']);
Route::get('/clear-attendance-cache', [EmployeeDashboard::class, 'clearEmployeeCache']);


Route::post('employee-store', [EmployeeController::class, 'employeeStore']);
Route::get('employee-single/{id}', [EmployeeController::class, 'employeeSingle']);
Route::post('employee-update/{id}', [EmployeeController::class, 'employeeUpdate']);
Route::post('employee-login-update/{id}', [EmployeeController::class, 'employeeLoginUpdate']);
Route::post('employee-rfid-update/{id}', [EmployeeController::class, 'employeeRFIDUpdate']);


Route::get('employee-announcements/{id}', [EmployeeController::class, 'employeeAnnouncements']);
Route::get('employee-today-announcements/{id}', [EmployeeController::class, 'employeeTodayAnnouncements']);

Route::get('department-employee', [DepartmentController::class, 'departmentEmployee']);
Route::get('download-emp-pic/{id}/{name}', [EmployeeController::class, 'downloadEmployeePic']);
Route::get('download-emp-documents/{id}/{file_name}', [EmployeeController::class, 'downloadEmployeeDocuments']);
Route::get('download-employee-profile-pdf/{id}', [EmployeeController::class, 'downloadEmployeeProfilepdf']);
Route::get('download-employee-profile-pdf-view/{id}', [EmployeeController::class, 'downloadEmployeeProfilepdfView']);


Route::get('/donwload_storage_file', [EmployeeController::class, 'donwnloadStorageFile']);

Route::get('default-attendance-missing', [EmployeeController::class, 'defaultAttendanceForMissing']);
Route::get('default-attendance-missing-schedule-ids', [EmployeeController::class, 'defaultAttendanceForMissingScheduleIds']);



Route::post('delete-employee-from-device', [EmployeeController::class, 'deleteEmployeeFromDevice']);


Route::get('get-employee-device-details', [DeviceController::class, 'getDevicePersonDetails']);
