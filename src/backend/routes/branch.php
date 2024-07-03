<?php

use App\Http\Controllers\CompanyBranchController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/branch', CompanyBranchController::class);
Route::post('/branch/{id}', [CompanyBranchController::class, "update"]);
Route::get('/branches_list', [CompanyBranchController::class, "branchesList"]);
Route::get('/branch-list', [CompanyBranchController::class, "dropdownList"]);

