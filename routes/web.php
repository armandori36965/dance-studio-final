<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseTemplateController;
use App\Http\Controllers\SchoolEventController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;

// 預設首頁，直接導向登入頁面
Route::get('/', function () {
    return redirect()->route('login');
});

// --- 手動定義所有認證相關的路由 ---

// Login Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout'); // 已修正

// Registration Routes
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']); // 已修正

// Password Reset Routes
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email Verification Routes
Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');


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