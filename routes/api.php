<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\LocationController;

// 這是給前端 JavaScript 呼叫的 API
// 它會根據傳入的校區 ID，回傳該校區的所有地點資料
//Route::get('/campuses/{campus}/locations', [LocationController::class, 'getLocationsByCampus']);