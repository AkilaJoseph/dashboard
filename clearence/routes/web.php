<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ClearanceController;
use App\Http\Controllers\Officer\DashboardController as OfficerDashboard;
use App\Http\Controllers\Officer\ApprovalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ClearanceController as AdminClearanceController;
use App\Http\Controllers\Admin\SIMSController;
use App\Http\Controllers\Admin\PwaDebugController;
use App\Http\Controllers\Admin\PushCampaignController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\PendingSyncController;
use App\Http\Controllers\Student\NotificationSettingsController;
use App\Http\Controllers\Student\StudentCardController;
use App\Http\Controllers\Officer\DepartmentScanController;
use App\Http\Controllers\CertificateVerifyController;
use App\Http\Controllers\Admin\LedgerController;
use App\Http\Controllers\LocaleController;

// Public routes
Route::get('/', function () { return redirect('/login'); });
Route::get('/offline', fn() => view('offline'))->name('offline');
Route::get('/verify/{clearance}', [CertificateVerifyController::class, 'show'])->name('verify');
Route::get('/locale/{locale}',    [LocaleController::class, 'switch'])->name('locale.switch');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Shared auth routes (all roles)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
});

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
    Route::get('/pending-sync', [PendingSyncController::class, 'index'])->name('pending-sync');
    Route::get('/notification-settings', [NotificationSettingsController::class, 'index'])->name('notification-settings');
    Route::patch('/notification-settings', [NotificationSettingsController::class, 'update'])->name('notification-settings.update');
    Route::post('/notification-settings/remove-device', [NotificationSettingsController::class, 'removeDevice'])->name('notification-settings.remove-device');

    Route::get('/my-card', [StudentCardController::class, 'show'])->name('my-card');

    // Store route gets idempotency protection for offline draft sync replays.
    // All other resource actions are unchanged.
    Route::post('/clearances', [ClearanceController::class, 'store'])
        ->name('clearances.store')
        ->middleware('idempotency');
    Route::resource('clearances', ClearanceController::class)->except(['store']);

    Route::get('/clearances/{clearance}/certificate', [ClearanceController::class, 'downloadCertificate'])
        ->name('clearances.certificate');
});

// Officer routes
Route::middleware(['auth', 'role:officer'])->prefix('officer')->name('officer.')->group(function () {
    Route::get('/dashboard', [OfficerDashboard::class, 'index'])->name('dashboard');
    Route::get('/scan',      [DepartmentScanController::class, 'show'])->name('scan');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('departments', DepartmentController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    // Clearance management
    Route::get('/clearances', [AdminClearanceController::class, 'index'])->name('clearances.index');
    Route::get('/clearances/{clearance}', [AdminClearanceController::class, 'show'])->name('clearances.show');
    Route::post('/clearances/{clearance}/approvals/{approval}/override', [AdminClearanceController::class, 'override'])
        ->name('clearances.override');
    // PWA diagnostics
    Route::get('/debug/pwa', [PwaDebugController::class, 'index'])->name('debug.pwa');

    // Push campaigns
    Route::get('/push-campaigns',          [PushCampaignController::class, 'index'])->name('push-campaigns.index');
    Route::get('/push-campaigns/create',   [PushCampaignController::class, 'create'])->name('push-campaigns.create');
    Route::post('/push-campaigns',         [PushCampaignController::class, 'store'])->name('push-campaigns.store');
    Route::post('/push-campaigns/preview', [PushCampaignController::class, 'preview'])->name('push-campaigns.preview');

    // SIMS Integration
    Route::get('/sims/settings', [SIMSController::class, 'settings'])->name('sims.settings');
    Route::post('/sims/settings', [SIMSController::class, 'saveSettings'])->name('sims.settings.save');
    Route::get('/sims/sync', [SIMSController::class, 'sync'])->name('sims.sync');
    Route::post('/sims/fetch', [SIMSController::class, 'fetchStudent'])->name('sims.fetch');
    Route::post('/sims/import', [SIMSController::class, 'importStudent'])->name('sims.import');
    Route::post('/sims/resync/{user}', [SIMSController::class, 'resync'])->name('sims.resync');

    // Certificate ledger
    Route::get('/ledger',              [LedgerController::class, 'index'])->name('ledger.index');
    Route::get('/ledger/verify-chain', [LedgerController::class, 'verifyChain'])->name('ledger.verify-chain');
});
