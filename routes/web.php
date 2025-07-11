<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseTemplateController;
use App\Http\Controllers\SchoolEventController;

// 預設首頁，直接導向登入頁面
Route::get('/', function () {
    return redirect()->route('login');
});

// Laravel 預設的登入、註冊、忘記密碼等路由
Auth::routes();

// --- 需要登入才能訪問的區域 ---
Route::middleware(['auth'])->group(function () {

    // 行事曆主頁
    Route::get('/home', [ScheduleController::class, 'index'])->name('home');
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

    // 課程 CRUD (Create, Read, Update, Delete)
    Route::post('/courses', [ScheduleController::class, 'store'])->name('courses.store');
    Route::put('/courses/{course}', [ScheduleController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [ScheduleController::class, 'destroy'])->name('courses.destroy');

    // 校區管理 API
    Route::post('/campuses', [CampusController::class, 'store'])->name('campuses.store');
    Route::put('/campuses/{campus}', [CampusController::class, 'update'])->name('campuses.update');
    Route::delete('/campuses/{campus}', [CampusController::class, 'destroy'])->name('campuses.destroy');

    // 地點管理 API
    Route::get('/api/campuses/{campus}/locations', [LocationController::class, 'getLocationsByCampus']);
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

    // 老師管理 API
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

    // 課程範本管理 API
    Route::post('/course-templates', [CourseTemplateController::class, 'store'])->name('course-templates.store');
    Route::put('/course-templates/{courseTemplate}', [CourseTemplateController::class, 'update'])->name('course-templates.update');
    Route::delete('/course-templates/{courseTemplate}', [CourseTemplateController::class, 'destroy'])->name('course-templates.destroy');
    
    // 校務事件管理 API (如果需要的話)
    Route::post('/school-events', [SchoolEventController::class, 'store'])->name('school-events.store');
    Route::put('/school-events/{schoolEvent}', [SchoolEventController::class, 'update'])->name('school-events.update');
    Route::delete('/school-events/{schoolEvent}', [SchoolEventController::class, 'destroy'])->name('school-events.destroy');

});
