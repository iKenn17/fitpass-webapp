<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Owner\AnalyticsController;
use App\Http\Controllers\Owner\AuditLogController;
use App\Http\Controllers\Owner\CheckInController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\MemberController;
use App\Http\Controllers\Owner\StaffController;
use App\Http\Middleware\EnsureOwner;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:6,1')->name('login.attempt');
});

Route::middleware(['auth', EnsureOwner::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/analytics', AnalyticsController::class)->name('analytics');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::patch('/staff/{user}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');

    Route::get('/check-ins', [CheckInController::class, 'index'])->name('checkins.index');
    Route::get('/check-ins/create', [CheckInController::class, 'create'])->name('checkins.create');
    Route::post('/check-ins', [CheckInController::class, 'store'])->name('checkins.store');
    Route::get('/check-ins/export', [CheckInController::class, 'export'])->name('checkins.export');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
});
