<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==================== الصفحات التسويقية ====================
Route::prefix('')->name('marketing.')->group(function () {
    Route::get('/features', fn() => view('marketing.features'))->name('features');
    Route::get('/pricing',  fn() => view('marketing.pricing'))->name('pricing');
    Route::get('/faq',      fn() => view('marketing.faq'))->name('faq');
    Route::get('/contact',  fn() => view('marketing.contact'))->name('contact');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects — Phase 4
    Route::resource('projects', ProjectController::class)->except(['store'])->names('projects');
    Route::post('projects', [ProjectController::class, 'store'])
        ->middleware('subscription:projects')
        ->name('projects.store');

    // ==================== عروض الأسعار ====================
    Route::prefix('quotes')->name('quotes.')->group(function () {
        Route::get('/',                          [QuoteController::class, 'index'])->name('index');
        Route::get('/create',                    [QuoteController::class, 'create'])->name('create');
        Route::post('/',                         [QuoteController::class, 'store'])->middleware('subscription:quotes')->name('store');
        Route::get('/{ulid}',                    [QuoteController::class, 'show'])->name('show');
        Route::get('/{ulid}/edit',               [QuoteController::class, 'edit'])->name('edit');
        Route::put('/{ulid}',                    [QuoteController::class, 'update'])->name('update');
        Route::delete('/{ulid}',                 [QuoteController::class, 'destroy'])->name('destroy');
        Route::post('/{ulid}/mark-sent',         [QuoteController::class, 'markSent'])->name('mark-sent');
        Route::post('/{ulid}/convert',           [QuoteController::class, 'convertToInvoice'])->name('convert');
    });

    // ==================== الفواتير ====================
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/',                    [InvoiceController::class, 'index'])->name('index');
        Route::get('/create',              [InvoiceController::class, 'create'])->name('create');
        Route::post('/',                   [InvoiceController::class, 'store'])->middleware('subscription:invoices')->name('store');
        Route::get('/{invoice}',           [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit',      [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}',           [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}',        [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/{invoice}/mark-sent',    [InvoiceController::class, 'markSent'])->name('mark-sent');
        Route::post('/{invoice}/mark-paid',    [InvoiceController::class, 'markPaid'])->name('mark-paid');
        Route::post('/{invoice}/cancel',       [InvoiceController::class, 'cancel'])->name('cancel');
        Route::post('/{invoice}/send-client',  [InvoiceController::class, 'sendToClient'])->name('send-client');
        Route::get('/{invoice}/pdf',           [InvoiceController::class, 'downloadPdf'])->name('pdf');
        Route::get('/reminders/whatsapp',      [InvoiceController::class, 'whatsappReminders'])->name('reminders.whatsapp');
        Route::post('/reminders/{log}/mark-sent', [InvoiceController::class, 'markReminderSent'])->name('reminders.mark-sent');
    });

    // Clients — تم نقله إلى CRM Module (routes/crm.php via CRMServiceProvider)
    // Route::resource('clients', ...) — replaced by full CRM module

    // Team Members
    Route::resource('team', TeamMemberController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('team');

    // Pay team member for a service
    Route::post('projects/{project}/pay-team/{memberId}', [ProjectController::class, 'payTeamMember'])->name('projects.pay-team');
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');

    // ── الصناديق / الخزائن ──────────────────────────────────────────────
    Route::resource('wallets', WalletController::class);
    Route::get('wallets-transfer', [WalletController::class, 'transferCreate'])->name('wallets.transfer.create');
    Route::post('wallets-transfer', [WalletController::class, 'transferStore'])->name('wallets.transfer.store');

    // متوسط هامش الخدمة تاريخياً (للتنبيهات الذكية)
    Route::get('projects/service-margin-history/{serviceId}', [ProjectController::class, 'serviceMarginHistory'])->name('projects.service-margin-history');

    // Services (catalog)
    Route::resource('services', ServiceController::class)
        ->only(['index', 'store', 'destroy'])
        ->names('services');
    // Quick-create service via JSON (from project form)
    Route::post('services/quick', [ServiceController::class, 'quickStore'])->name('services.quick-store');

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

    // Help Center
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');

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
    Route::post('/settings/invoice',           [SettingsController::class, 'updateInvoice'])->name('settings.invoice');

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
        Route::get('/upgrade',   [BillingController::class, 'upgrade'])->name('upgrade');
        Route::post('/checkout', [BillingController::class, 'checkout'])->name('checkout');
        Route::get('/success',   [BillingController::class, 'success'])->name('success');
        Route::get('/failed',    [BillingController::class, 'failed'])->name('failed');
        Route::post('/portal',   [BillingController::class, 'portal'])->name('portal');

        // Togo Payment Gateway — redirect callbacks (بعد الدفع أو الإلغاء)
        Route::get('/togo/callback', [BillingController::class, 'togoCallback'])->name('togo.callback');
        Route::get('/togo/cancel',   [BillingController::class, 'togoCancel'])->name('togo.cancel');
    });

    // Profile (Breeze)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ══════════════════════════════════════════════════════
// بوابة العميل لعروض الأسعار — بدون Auth (رابط عام مُؤمَّن بـ token)
// ══════════════════════════════════════════════════════
// ── صفحة الفاتورة العامة للعميل (signed URL — بدون تسجيل دخول) ──────
Route::get('/invoice/{ulid}/view', [InvoiceController::class, 'publicView'])
    ->name('invoices.public-view')
    ->middleware('signed');

Route::prefix('q')->name('quotes.')->group(function () {
    Route::get('/{token}',         [QuoteController::class, 'portal'])->name('portal');
    Route::post('/{token}/accept', [QuoteController::class, 'accept'])->name('accept');
    Route::post('/{token}/reject', [QuoteController::class, 'reject'])->name('reject');
});

// Stripe Webhook — بدون Auth (Stripe يتحقق بـ signature)
Route::post('/stripe/webhook', [BillingController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('stripe.webhook');

// ─── Admin Impersonation ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/impersonate/{userId}', [\App\Http\Controllers\Admin\ImpersonateController::class, 'impersonate'])
        ->name('admin.impersonate');
    Route::get('/admin/impersonate-leave', [\App\Http\Controllers\Admin\ImpersonateController::class, 'leave'])
        ->name('admin.impersonate.leave');
});

require __DIR__.'/auth.php';
