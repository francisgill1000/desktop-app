<?php

use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Reports\DailyController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\WeeklyController;
use App\Http\Controllers\Reports\MonthlyController;
use App\Http\Controllers\Reports\MonthlyMergeController;
use App\Http\Controllers\Reports\MonthlyMergeJobController;
use App\Http\Controllers\Reports\MonthlyMimoController;
use App\Http\Controllers\Reports\PDFController;
use App\Http\Controllers\Reports\PDFTestController;
use App\Http\Controllers\Reports\WeeklyMimoController;


Route::get('/process_reports', [DailyController::class, 'process_reports']);


Route::get('report', [ReportController::class, 'index']);


//daily
Route::get('/daily', [DailyController::class, 'daily']);
Route::get('/daily_download_pdf', [DailyController::class, 'daily_download_pdf']);
Route::get('/daily_generate_pdf', [DailyController::class, 'daily_generate_pdf']);
Route::get('/daily_download_csv', [ReportController::class, 'general_download_csv']);

//multi in out
// -> csv
Route::get('/multi_in_out_daily_download_csv', [ReportController::class, 'multi_in_out_daily_download_csv']);
Route::get('/multi_in_out_monthly_download_csv', [MonthlyController::class, 'multi_in_out_monthly_download_csv']);
Route::get('/multi_in_out_weekly_download_csv', [WeeklyController::class, 'multi_in_out_weekly_download_csv']);

// -> pdf view
Route::get('/multi_in_out_daily', [DailyController::class, 'mimo_daily_pdf']);
Route::get('/multi_in_out_weekly', [WeeklyController::class, 'multi_in_out_weekly_pdf']);
Route::get('/multi_in_out_monthly', [MonthlyController::class, 'multi_in_out_monthly_pdf']);


// -> pdf download
Route::get('/multi_in_out_daily_download_pdf', [DailyController::class, 'mimo_daily_download']);
Route::get('/multi_in_out_weekly_download_pdf', [WeeklyController::class, 'multi_in_out_weekly_download_pdf']);
Route::get('/multi_in_out_monthly_download_pdf', [MonthlyController::class, 'multi_in_out_monthly_download_pdf']);


// -> pdf cron
Route::get('report_multi_in_out', [ReportController::class, 'multiInOut']);
Route::get('csv_pdf', [MonthlyController::class, 'csvPdf']);

Route::get('/generateSummaryReport/{id}', [DailyController::class, 'generateSummaryReport']);
Route::get('/generatePresentReport/{id}', [DailyController::class, 'generatePresentReport']);
Route::get('/generateAbsentReport/{id}', [DailyController::class, 'generateAbsentReport']);
Route::get('/generateMissingReport/{id}', [DailyController::class, 'generateMissingReport']);
Route::get('/generateManualReport/{id}', [DailyController::class, 'generateManualReport']);

// weekly
Route::get('/weekly', [WeeklyController::class, 'weekly']);
Route::get('/weekly_download_pdf', [WeeklyController::class, 'weekly_download_pdf']);
Route::get('/weekly_generate_pdf', [WeeklyController::class, 'weekly_generate_pdf']);
Route::get('/weekly_download_csv', [WeeklyController::class, 'weekly_download_csv']);



//monthly _OLD without merge 
Route::get('/monthly', [MonthlyController::class, 'monthly']);
Route::get('/monthly_download_pdf', [MonthlyController::class, 'monthly_download_pdf']);
Route::get('/monthly_generate_pdf', [MonthlyController::class, 'monthly_generate_pdf']);
Route::get('/monthly_download_csv', [MonthlyController::class, 'monthly_download_csv']);

// //monthly reports merge 
// Route::get('/monthly_merge', [MonthlyMergeController::class, 'monthly']);

// //monthly
// Route::get('/monthly', [MonthlyMergeController::class, 'monthly']);
// Route::get('/monthly_download_pdf', [MonthlyMergeController::class, 'monthly_download_pdf']);
// Route::get('/monthly_generate_pdf', [MonthlyMergeController::class, 'monthly_generate_pdf']);
// Route::get('/monthly_download_csv', [MonthlyMergeController::class, 'monthly_download_csv']);

// Route::get('/multi_in_out_monthly', [MonthlyMergeController::class, 'multi_in_out_monthly_pdf']);
// Route::get('/multi_in_out_monthly_download_pdf', [MonthlyMergeController::class, 'multi_in_out_monthly_download_pdf']);

// Route::get('/multi_in_out_monthly_download_csv', [MonthlyMergeController::class, 'multi_in_out_monthly_download_csv']);



// //monthly reports Jobs Queue merge 
// Route::get('/monthly_merge_job', [MonthlyMergeJobController::class, 'monthly']);
// Route::get('/monthly_download_pdf_merge_job', [MonthlyMergeJobController::class, 'monthly_download']);
// Route::get('/download_finalfile', [MonthlyMergeJobController::class, 'downloadFinalfile']);
// Route::get('/view_finalfile', [MonthlyMergeJobController::class, 'viewFinalfile']);
// Route::get('/verify_generated_pdf_file', [MonthlyMergeJobController::class, 'verifyGeneratedPDFFile']);






//multi in out


//for testing static
Route::get('/daily_summary', [PDFController::class, 'daily_summary']);
Route::get('/weekly_summary', [PDFController::class, 'weekly_summary']);
Route::get('/monthly_summary', [PDFController::class, 'monthly_summary']);




Route::get('/chart_html', [Controller::class, 'chart_html']);
Route::get('/test_chart', [TestController::class, 'index']);

Route::get('/test_week', [TestController::class, 'test_week']);


Route::get('/daily_mimo', [Controller::class, 'mimo']);
Route::get('/weekly_mimo', [WeeklyMimoController::class, 'weekly']);
Route::get('/monthly_mimo', [MonthlyMimoController::class, 'monthly']);




//static
Route::get('/daily_access_control', [PDFController::class, 'dailyAccessControl']);
Route::get('/weekly_access_control', [PDFController::class, 'weeklyAccessControl']);
Route::get('/monthly_access_control', [PDFController::class, 'monthlyAccessControl']);
Route::get('/monthly_access_control_v1', [PDFController::class, 'monthlyAccessControlV1']);


Route::get('/access_control_by_device', [PDFController::class, 'monthlyAccessControlByDevice']);

Route::get('/monthly_access_control_count', [PDFController::class, 'monthlyAccessControlCount']);

Route::get('/generatePresentReportTest/{id}', [PDFTestController::class, 'generatePresentReport']);


// access_control
Route::get('/access_control_report_print_pdf', [AccessControlController::class, 'access_control_report_print_pdf']);
Route::get('/access_control_report_download_pdf', [AccessControlController::class, 'access_control_report_download_pdf']);

Route::get('/document_expiry_print_pdf', [EmployeeController::class, 'document_expiry_print_pdf']);


Route::get('/testPDF', [PDFController::class, 'testPDF']);
Route::get('/accessControlReport_print_pdf', [PDFController::class, 'accessControlReportPrint']);
Route::get('/accessControlReport_download_pdf', [PDFController::class, 'accessControlReportDownload']);
