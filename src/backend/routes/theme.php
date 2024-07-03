<?php

use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('theme', ThemeController::class);
Route::get('theme_count', [ThemeController::class, "theme_count"]);
Route::get('dashbaord_attendance_count', [ThemeController::class, "dashboardCount"]);
Route::get('dashboard_counts_last_7_days', [ThemeController::class, "dashboardGetCountslast7Days"]);
Route::get('dashboard_get_count_department', [ThemeController::class, "dashboardGetCountDepartment"]);
Route::get('previous_week_attendance_count/{id}', [ThemeController::class, "previousWeekAttendanceCount"]);
Route::get('dashboard_Get_Counts_today_multi_general', [ThemeController::class, "dashboardGetCountsTodayMultiGeneral"]);
Route::get('dashboard_get_counts_today_hour_in_out', [ThemeController::class, "dashboardGetCountsTodayHourInOut"]);

Route::get('dashboard_get_visitor_counts_today_hour_in_out', [ThemeController::class, "dashboardGetVisitorCountsTodayHourInOut"]);
