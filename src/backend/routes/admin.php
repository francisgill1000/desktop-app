<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssignModuleController;
use App\Http\Controllers\AssignPermissionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceStatusController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SdkLogcsvfileController;
use App\Http\Controllers\Shift\MultiInOutShiftController;
use App\Http\Controllers\Shift\SingleShiftController;
use App\Http\Controllers\TradeLicenseController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('admin', AdminController::class);

// Auth
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/loginwith_otp', [AuthController::class, 'loginwithOTP']);
Route::post('/check_otp/{key}', [AuthController::class, 'verifyOTP']);

Route::post('/employee/login', [EmployeeController::class, 'login']);
Route::get('/employee/me', [EmployeeController::class, 'me'])->middleware('auth:sanctum');


Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// reset password
Route::post('/reset-password', [ResetPasswordController::class, 'sendCode']);
Route::post('/check-code', [ResetPasswordController::class, 'checkCode']);
Route::post('/new-password', [ResetPasswordController::class, 'newPassword']);

// Assign Permission
Route::post('assign-permission/delete/selected', [AssignPermissionController::class, 'dsr']);
Route::get('assign-permission/search/{key}', [AssignPermissionController::class, 'search']); // search records
Route::get('assign-permission/nars', [AssignPermissionController::class, 'notAssignedRoleIds']);
Route::resource('assign-permission', AssignPermissionController::class);

// User
Route::apiResource('users', UserController::class);
Route::get('users/search/{key}', [UserController::class, 'search']);
Route::post('users/delete/selected', [UserController::class, 'deleteSelected']);

//  Company
Route::get('company/list', [CompanyController::class, 'CompanyList']);

Route::apiResource('company', CompanyController::class)->except('update');
Route::post('company/{id}/update', [CompanyController::class, 'updateCompany']);
Route::post('company/{id}/update/contact', [CompanyController::class, 'updateContact']);
Route::post('company/{id}/update/user', [CompanyController::class, 'updateCompanyUser']);
Route::post('company/{id}/update/user_whatsapp', [CompanyController::class, 'updateCompanyUserWhatsapp']);
Route::post('company/{id}/update/whatsapp_settings', [CompanyController::class, 'updateCompanyWhatsappSettings']);
Route::post('company/{id}/update/modules_settings', [CompanyController::class, 'updateCompanyModulesSettings']);


Route::post('company/{id}/update/geographic', [CompanyController::class, 'updateCompanyGeographic']);
Route::post('company/validate', [CompanyController::class, 'validateCompany']);
Route::post('company/contact/validate', [CompanyController::class, 'validateContact']);
Route::post('company/user/validate', [CompanyController::class, 'validateCompanyUser']);
Route::post('company/update/user/validate', [CompanyController::class, 'validateCompanyUserUpdate']);
Route::get('company/search/{key}', [CompanyController::class, 'search']);
Route::get('company/{id}/branches', [CompanyController::class, 'branches']);
Route::get('company/{id}/devices', [CompanyController::class, 'devices']);
Route::get('UpdateCompanyIds', [CompanyController::class, 'UpdateCompanyIds']);

Route::post('company/{id}/trade-license', [TradeLicenseController::class, 'store']);

//  Permission
Route::apiResource('permission', PermissionController::class);
Route::get('user/{id}/permission', [PermissionController::class, 'permissions']);
Route::get('permission/search/{key}', [PermissionController::class, 'search']);
Route::post('permission/delete/selected', [PermissionController::class, 'deleteSelected']);

// Role
Route::apiResource('role', RoleController::class);
Route::get('user/{id}/role', [RoleController::class, 'roles']);
Route::get('role/search/{key}', [RoleController::class, 'search']);
Route::get('role/permissions/search/{key}', [RoleController::class, 'searchWithRelation']);
Route::get('role/{id}/permissions', [RoleController::class, 'getPermission']);
Route::post('role/{id}/permissions', [RoleController::class, 'assignPermission']);
Route::post('role/delete/selected', [RoleController::class, 'deleteSelected']);
Route::get('role-list', [RoleController::class, 'dropdownList']);

// Branch
// Route::apiResource('branch', BranchController::class)->except('update');
// Route::post('branch/{id}/update', [BranchController::class, 'update']);
// Route::post('branch/{id}/update/contact', [BranchController::class, 'updateContact']);
// Route::post('branch/{id}/update/user', [BranchController::class, 'updateBranchUserUpdate']);
// Route::post('branch/validate', [BranchController::class, 'validateBranch']);
// Route::post('branch/contact/validate', [BranchController::class, 'validateContact']);
// Route::post('branch/user/validate', [BranchController::class, 'validateBranchUser']);
// Route::post('branch/update/user/validate', [BranchController::class, 'validateBranchUserUpdate']);
// Route::get('branch/search/{key}', [BranchController::class, 'search']);



// Module
Route::apiResource('module', ModuleController::class);
Route::get('module/search/{key}', [ModuleController::class, 'search']);
Route::post('module/delete/selected', [ModuleController::class, 'deleteSelected']);

// Assign Permission
Route::post('assign-module/delete/selected', [AssignModuleController::class, 'dsr']);
Route::get('assign-module/search/{key}', [AssignModuleController::class, 'search']);
Route::get('assign-module/nacs', [AssignModuleController::class, 'notAssignedCompanyIds']);
Route::resource('assign-module', AssignModuleController::class);

//Testing Routes for Cron Jobs
Route::get('SyncCompanyIdsWithDevices', [AttendanceLogController::class, 'SyncCompanyIdsWithDevices']);

Route::get('SyncAttendance', [AttendanceController::class, 'SyncAttendance']);

Route::get('ProcessAttendance', [AttendanceController::class, 'ProcessAttendance']);
Route::get('processByManual', [MultiInOutShiftController::class, 'processByManual']);
Route::get('processByManualForSingleShift', [SingleShiftController::class, 'processByManual']);

// Route::get('SyncAbsentForMultipleDays', [AttendanceController::class, 'SyncAbsentForMultipleDays']);
// Route::get('SyncAbsentForMultipleDays', [AttendanceController::class, 'SyncAbsentForMultipleDays']);

Route::get('reset_file/{token}/{file}', [CommonController::class, 'destroy']);

Route::get('downloadfiles', [SdkLogcsvfileController::class, 'list']);
Route::get('download/{key}', [SdkLogcsvfileController::class, 'download']);
