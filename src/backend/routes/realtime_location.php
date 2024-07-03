<?php

use App\Http\Controllers\RealTimeLocationController;
use Illuminate\Support\Facades\Route;

Route::get('/realtime_location', [RealTimeLocationController::class, 'index']);
Route::post('/realtime_location', [RealTimeLocationController::class, 'store']);
