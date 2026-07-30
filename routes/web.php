<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\ArmController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GivingController;
use App\Http\Controllers\GroupChurchController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/auth', [AuthController::class, 'show'])->name('login');
    Route::post('/auth', [AuthController::class, 'login'])->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:zone_admin')->group(function () {
        Route::get('/search', [SearchController::class, 'index'])->name('search');
        Route::post('/search', [SearchController::class, 'search'])->name('search.run');

        Route::get('/groups', [GroupChurchController::class, 'index'])->name('groups.index');
        Route::post('/groups', [GroupChurchController::class, 'store'])->name('groups.store');

        Route::get('/statements', [StatementController::class, 'index'])->name('statements.index');
        Route::post('/statements', [StatementController::class, 'store'])->name('statements.store');
        Route::post('/statements/{statement}/send', [StatementController::class, 'send'])->name('statements.send');

        Route::get('/arms', [ArmController::class, 'index'])->name('arms.index');
        Route::post('/arms', [ArmController::class, 'store'])->name('arms.store');
        Route::patch('/arms/{arm}', [ArmController::class, 'update'])->name('arms.update');
    });

    Route::middleware('role:zone_admin,group_admin')->group(function () {
        Route::get('/churches', [ChurchController::class, 'index'])->name('churches.index');
        Route::post('/churches', [ChurchController::class, 'store'])->name('churches.store');
    });

    Route::middleware('role:zone_admin,group_admin,church_admin')->group(function () {
        Route::get('/partners/export', [PartnerController::class, 'export'])->name('partners.export');
        Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
        Route::post('/partners', [PartnerController::class, 'store'])->name('partners.store');

        Route::get('/givings', [GivingController::class, 'index'])->name('givings.index');
        Route::post('/givings', [GivingController::class, 'store'])->name('givings.store');

        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');

        Route::get('/upload', [UploadController::class, 'index'])->name('upload.index');
        Route::post('/upload', [UploadController::class, 'import'])->name('upload.import');

        Route::get('/alerts/export', [AlertController::class, 'export'])->name('alerts.export');
        Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
        Route::patch('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    });

    Route::middleware('role:zone_admin')->post('/alerts/thresholds', [AlertController::class, 'saveThreshold'])->name('alerts.thresholds.save');
});