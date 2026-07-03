<?php

use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UnmatchedEmailController as AdminUnmatchedEmailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController;
use App\Http\Controllers\Client\SettingsController as ClientSettingsController;
use App\Http\Controllers\InboundEmailController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect(auth()->user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard'))
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth')->name('notifications.index');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('clients', AdminClientController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('/invoices/{invoice}', [AdminInvoiceController::class, 'update'])->name('invoices.update');
    Route::get('/invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/preview', [AdminInvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('/unmatched-emails', [AdminUnmatchedEmailController::class, 'index'])->name('unmatched-emails.index');
    Route::get('/unmatched-emails/{email}', [AdminUnmatchedEmailController::class, 'show'])->name('unmatched-emails.show');
    Route::patch('/unmatched-attachments/{attachment}/transfer', [AdminUnmatchedEmailController::class, 'transfer'])->name('unmatched-attachments.transfer');
    Route::get('/unmatched-attachments/{attachment}/download', [AdminUnmatchedEmailController::class, 'download'])->name('unmatched-attachments.download');
    Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings/profile', [AdminSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::patch('/settings/email', [AdminSettingsController::class, 'updateEmailSettings'])->name('settings.email.update');
    Route::patch('/settings/storage', [AdminSettingsController::class, 'updateStorageSettings'])->name('settings.storage.update');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'role:client'])->prefix('portal')->name('client.')->group(function (): void {
    Route::get('/dashboard', ClientDashboardController::class)->name('dashboard');
    Route::resource('invoices', ClientInvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/invoices/{invoice}/download', [ClientInvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/preview', [ClientInvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('/settings', [ClientSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [ClientSettingsController::class, 'update'])->name('settings.update');
});

Route::post('/inbound/email', [InboundEmailController::class, 'store'])->name('inbound.email.store');
