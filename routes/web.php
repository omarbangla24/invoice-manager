<?php

use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\JobRequestController as AdminJobRequestController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UnmatchedEmailController as AdminUnmatchedEmailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController;
use App\Http\Controllers\Client\JobRequestController as ClientJobRequestController;
use App\Http\Controllers\Client\SettingsController as ClientSettingsController;
use App\Http\Controllers\InboundEmailController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect(auth()->user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard'))
        : redirect()->route('login');
})->name('home');

Route::middleware(['guest', 'throttle:5,1'])->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/forgot-password', fn () => view('auth.forgot-password'))->middleware('guest')->name('password.request');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth')->name('notifications.index');
Route::get('/notifications/feed', [NotificationController::class, 'feed'])->middleware('auth')->name('notifications.feed');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->middleware('auth')->name('notifications.read');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->middleware('auth')->name('notifications.read-all');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('clients', AdminClientController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('/invoices/{invoice}', [AdminInvoiceController::class, 'update'])->name('invoices.update');
    Route::patch('/invoices/{invoice}/details', [AdminInvoiceController::class, 'updateDetails'])->name('invoices.details.update');
    Route::delete('/invoices/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/preview', [AdminInvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('/unmatched-emails', [AdminUnmatchedEmailController::class, 'index'])->name('unmatched-emails.index');
    Route::get('/unmatched-emails/{email}', [AdminUnmatchedEmailController::class, 'show'])->name('unmatched-emails.show');
    Route::delete('/unmatched-emails/{email}', [AdminUnmatchedEmailController::class, 'destroyEmail'])->name('unmatched-emails.destroy');
    Route::patch('/unmatched-attachments/{attachment}/transfer', [AdminUnmatchedEmailController::class, 'transfer'])->name('unmatched-attachments.transfer');
    Route::delete('/unmatched-attachments/{attachment}', [AdminUnmatchedEmailController::class, 'destroyAttachment'])->name('unmatched-attachments.destroy');
    Route::get('/unmatched-attachments/{attachment}/download', [AdminUnmatchedEmailController::class, 'download'])->name('unmatched-attachments.download');
    Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings/profile', [AdminSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::patch('/settings/email', [AdminSettingsController::class, 'updateEmailSettings'])->name('settings.email.update');
    Route::patch('/settings/storage', [AdminSettingsController::class, 'updateStorageSettings'])->name('settings.storage.update');
    Route::resource('jobs', AdminJobController::class)->except(['show']);
    Route::get('/jobs/{job}/attachment', [AdminJobController::class, 'downloadAttachment'])->name('jobs.attachment');
    Route::get('/job-requests', [AdminJobRequestController::class, 'index'])->name('job-requests.index');
    Route::get('/job-requests/{jobRequest}', [AdminJobRequestController::class, 'show'])->name('job-requests.show');
    Route::patch('/job-requests/{jobRequest}/status', [AdminJobRequestController::class, 'updateStatus'])->name('job-requests.update-status');
    Route::get('/job-requests/{jobRequest}/attachment', [AdminJobRequestController::class, 'downloadAttachment'])->name('job-requests.attachment');
});

Route::middleware(['auth', 'role:client'])->prefix('portal')->name('client.')->group(function (): void {
    Route::get('/dashboard', ClientDashboardController::class)->name('dashboard');
    Route::resource('invoices', ClientInvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/invoices/{invoice}/download', [ClientInvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/preview', [ClientInvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('/settings', [ClientSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [ClientSettingsController::class, 'update'])->name('settings.update');
    Route::get('/job-requests', [ClientJobRequestController::class, 'index'])->name('job-requests.index');
    Route::get('/job-requests/create', [ClientJobRequestController::class, 'create'])->name('job-requests.create');
    Route::post('/job-requests', [ClientJobRequestController::class, 'store'])->name('job-requests.store');
    Route::get('/job-requests/{jobRequest}', [ClientJobRequestController::class, 'show'])->name('job-requests.show');
    Route::get('/job-requests/{jobRequest}/attachment', [ClientJobRequestController::class, 'downloadAttachment'])->name('job-requests.attachment');
});

Route::post('/inbound/email', [InboundEmailController::class, 'store'])->middleware('throttle:60,1')->name('inbound.email.store');
