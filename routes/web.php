<?php

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrivacyNoticeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ViewingRequestController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [HomeController::class, 'login'])->name('login');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
});

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->middleware('throttle:10,1')
    ->name('auth.google.callback');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/privacy', [PrivacyNoticeController::class, 'show'])->name('privacy.show');

Route::middleware('auth')->group(function () {
    Route::get('/requests', [ViewingRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [ViewingRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [ViewingRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{viewingRequest}', [ViewingRequestController::class, 'show'])->name('requests.show');
    Route::get('/appointments', [ViewingRequestController::class, 'appointments'])->name('appointments.index');
    Route::post('/requests/{viewingRequest}/supplement', [ViewingRequestController::class, 'supplement'])->name('requests.supplement');
    Route::post('/requests/{viewingRequest}/confirm', [ViewingRequestController::class, 'confirmAppointment'])->name('requests.confirm');

    Route::middleware('role:cctv_admin')->group(function () {
        Route::get('/queue', [WorkflowController::class, 'queue'])->name('queue');
        Route::post('/requests/{viewingRequest}/technical', [WorkflowController::class, 'technical'])->name('requests.technical');
        Route::post('/requests/{viewingRequest}/more-info', [WorkflowController::class, 'moreInfo'])->name('requests.more');
        Route::post('/requests/{viewingRequest}/schedule', [WorkflowController::class, 'schedule'])->name('requests.schedule');
        Route::post('/requests/{viewingRequest}/viewing', [WorkflowController::class, 'viewing'])->name('requests.viewing');
        Route::post('/requests/{viewingRequest}/close', [WorkflowController::class, 'close'])->name('requests.close');
        Route::post('/inspections/{inspection}', [CameraController::class, 'inspect'])->name('inspections.update');
        Route::post('/tickets/{ticket}', [CameraController::class, 'ticket'])->name('tickets.update');
    });

    Route::middleware('role:cctv_admin,super_admin')->group(function () {
        Route::get('/cameras', [CameraController::class, 'index'])->name('cameras.index');
        Route::post('/cameras', [CameraController::class, 'store'])->name('cameras.store');
        Route::post('/cameras/gaps', [CameraController::class, 'gap'])->name('cameras.gaps');
    });

    Route::middleware('role:director,associate_dean,super_admin')->group(function () {
        Route::get('/reviews', [WorkflowController::class, 'inbox'])->name('reviews');
        Route::post('/requests/{viewingRequest}/decide', [WorkflowController::class, 'decide'])->name('requests.decide');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('role:pdpa_coordinator,super_admin')->group(function () {
        Route::get('/privacy/edit', [PrivacyNoticeController::class, 'edit'])->name('privacy.edit');
        Route::post('/privacy', [PrivacyNoticeController::class, 'store'])->name('privacy.store');
    });

    Route::middleware('role:pdpa_coordinator')->group(function () {
        Route::get('/pdpa', [WorkflowController::class, 'inbox'])->name('pdpa.inbox');
        Route::post('/requests/{viewingRequest}/pdpa', [WorkflowController::class, 'pdpa'])->name('requests.pdpa');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin/users', [UserRoleController::class, 'index'])->name('admin.users');
        Route::post('/admin/users/{user}/role', [UserRoleController::class, 'update'])->name('admin.users.role');
        Route::post('/admin/covers', [UserRoleController::class, 'cover'])->name('admin.covers.store');
        Route::get('/admin/audit', [ReportController::class, 'audit'])->name('admin.audit');
        Route::get('/admin/rules', [ReportController::class, 'rules'])->name('admin.rules');
        Route::post('/admin/rules/{rule}', [ReportController::class, 'updateRule'])->name('admin.rules.update');
    });

    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');
});
