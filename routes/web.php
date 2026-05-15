<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects — Phase 4
    Route::resource('projects', ProjectController::class)->except(['store'])->names('projects');
    Route::post('projects', [ProjectController::class, 'store'])
        ->middleware('subscription:projects')
        ->name('projects.store');

    // Categories — Phase 5
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Transactions — Phase 6
    Route::resource('transactions', TransactionController::class)->except(['store'])->names('transactions');
    Route::post('transactions', [TransactionController::class, 'store'])
        ->middleware('subscription:transactions')
        ->name('transactions.store');

    // Debts — Phase 8
    Route::resource('debts', DebtController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::post('/debts/{debt}/record-payment', [DebtController::class, 'recordPayment'])->name('debts.record-payment');
    Route::post('/debts/{debt}/mark-paid',      [DebtController::class, 'markAsPaid'])->name('debts.mark-paid');

    // Reports — Phase 9
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Onboarding — U.3
    Route::post('/onboarding/dismiss', [OnboardingController::class, 'dismiss'])->name('onboarding.dismiss');

    // Reports Export — U.2
    Route::get('/reports/export/pdf',   [ReportExportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [ReportExportController::class, 'excel'])->name('reports.export.excel');

    // Notifications — Phase 10
    Route::get('/notifications',                          [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read',               [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all',                [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}',                  [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/api/notifications/unread-count',         [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // Settings — Phase 12
    Route::get('/settings',                    [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/profile',          [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::patch('/settings/password',         [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::patch('/settings/preferences',      [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::delete('/settings/account',         [SettingsController::class, 'deleteAccount'])->name('settings.delete-account');

    // Budget — Phase 4.5
    Route::resource('budget', BudgetController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('budget');

    // Recurring Transactions — Phase 5.5
    Route::resource('recurring', RecurringController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/recurring/{recurring}/toggle',      [RecurringController::class, 'toggle'])->name('recurring.toggle');
    Route::post('/recurring/{recurring}/process-now', [RecurringController::class, 'processNow'])->name('recurring.process-now');

    // Billing — Phase 11
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/',          [BillingController::class, 'index'])->name('index');
        Route::post('/checkout', [BillingController::class, 'checkout'])->name('checkout');
        Route::get('/success',   [BillingController::class, 'success'])->name('success');
        Route::post('/portal',   [BillingController::class, 'portal'])->name('portal');
    });

    // Profile (Breeze)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Stripe Webhook — بدون Auth (Stripe يتحقق بـ signature)
Route::post('/stripe/webhook', [BillingController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('stripe.webhook');

require __DIR__.'/auth.php';
