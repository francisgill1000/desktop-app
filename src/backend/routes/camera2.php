<?php

use App\Http\Controllers\Camera2;
use Illuminate\Support\Facades\Route;


Route::post('camera2_push_events', [Camera2::class, 'camera2PushEvents']);
Route::get('camera2_push_events', [Camera2::class, 'camera2PushEvents']);
