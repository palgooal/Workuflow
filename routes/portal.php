<?php

use Illuminate\Support\Facades\Route;
use App\Modules\CRM\Http\Controllers\ClientPortalController;

/*
|--------------------------------------------------------------------------
| Client Portal Routes — بوابة العميل
|--------------------------------------------------------------------------
| المرجع: docs/CLIENTS-CRM-SPEC-V2.md — Sprint 8
| Prefix: /portal  |  أمان: C-04 Fix — hash tokens + rate limiting
|
| ⚠️ هذه المسارات مستقلة عن مسارات التطبيق الرئيسي.
| المصادقة تعتمد على ClientPortalToken (وليس User session).
*/

Route::prefix('portal')->name('portal.')->group(function () {

    // ==================== المصادقة ====================

    // صفحة إدخال الرمز
    Route::get('/auth',    [ClientPortalController::class, 'showAuthForm'])->name('auth');
    Route::post('/auth',   [ClientPortalController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [ClientPortalController::class, 'logout'])->name('logout');

    // صفحة طلب الوصول (معلومات — لا وظيفة أوتوماتيكية)
    Route::get('/access',  [ClientPortalController::class, 'showAccessForm'])->name('access');

    // ==================== لوحة العميل (تحتاج مصادقة) ====================

    Route::middleware(['portal.auth'])->group(function () {
        Route::get('/dashboard',      [ClientPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/invoices',       [ClientPortalController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{id}',  [ClientPortalController::class, 'invoiceShow'])->name('invoices.show');
        Route::get('/profile',        [ClientPortalController::class, 'profile'])->name('profile');
    });
});
