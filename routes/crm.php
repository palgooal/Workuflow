<?php

use Illuminate\Support\Facades\Route;
use App\Modules\CRM\Http\Controllers\ClientController;
use App\Modules\CRM\Http\Controllers\ClientTagController;
use App\Modules\CRM\Http\Controllers\ClientFollowUpController;
use App\Modules\CRM\Http\Controllers\ClientSegmentController;
use App\Modules\CRM\Http\Controllers\ClientImportController;
use App\Modules\CRM\Http\Controllers\ClientExportController;
use App\Modules\CRM\Http\Controllers\ClientCustomFieldController;
use App\Modules\CRM\Http\Controllers\ClientPortalTokenController;
use App\Modules\CRM\Http\Controllers\AutomationRuleController;

/*
|--------------------------------------------------------------------------
| CRM Routes — نظام إدارة العملاء المتقدم
|--------------------------------------------------------------------------
| المرجع: docs/CLIENTS-CRM-SPEC-V2.md
| Prefix: /clients  |  Middleware: auth + active.account
|
| ⚠️ ترتيب المسارات مهم جداً:
|    الـ prefix الثابتة (export, import, tags...) يجب أن تأتي قبل /{client}
|    وإلا سيلتقطها الـ wildcard ويرجع 404
*/

Route::middleware(['auth', 'active.account'])->group(function () {

    // ==================== العملاء ====================

    Route::prefix('clients')->name('clients.')->group(function () {

        // ── 1. المسارات الثابتة (بدون {client}) ──────────────────────────
        //    يجب أن تكون أولاً لتجنب التقاطها بـ /{client} wildcard

        Route::get('/',       [ClientController::class, 'index'])->name('index');
        Route::get('/create', [ClientController::class, 'create'])->name('create');
        Route::post('/',      [ClientController::class, 'store'])->name('store');

        // ==================== الوسوم ====================

        Route::prefix('tags')->name('tags.')->group(function () {
            Route::get('/',             [ClientTagController::class, 'index'])->name('index');
            Route::post('/',            [ClientTagController::class, 'store'])->name('store');
            Route::put('/{tag}',        [ClientTagController::class, 'update'])->name('update');
            Route::delete('/{tag}',     [ClientTagController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-assign', [ClientTagController::class, 'bulkAssign'])->name('bulk-assign');
            Route::patch('/reorder',    [ClientTagController::class, 'reorder'])->name('reorder');
        });

        // ==================== المتابعات ====================

        Route::prefix('follow-ups')->name('follow-ups.')->group(function () {
            Route::get('/',         [ClientFollowUpController::class, 'index'])->name('index');
            Route::get('/upcoming', [ClientFollowUpController::class, 'upcoming'])->name('upcoming');
            Route::post('/quick',   [ClientFollowUpController::class, 'storeGeneral'])->name('quick-store');
        });

        // ==================== الشرائح ====================

        Route::prefix('segments')->name('segments.')->group(function () {
            Route::get('/',                   [ClientSegmentController::class, 'index'])->name('index');
            Route::post('/',                  [ClientSegmentController::class, 'store'])->name('store');
            Route::post('/preview',           [ClientSegmentController::class, 'preview'])->name('preview');
            Route::post('/{segment}/execute', [ClientSegmentController::class, 'execute'])->name('execute');
            Route::post('/{segment}/pin',     [ClientSegmentController::class, 'pin'])->name('pin');
            Route::delete('/{segment}',       [ClientSegmentController::class, 'destroy'])->name('destroy');
        });

        // ==================== الاستيراد والتصدير ====================

        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/template', [ClientImportController::class, 'template'])->name('template');
            Route::post('/',        [ClientImportController::class, 'store'])->name('store');
            Route::get('/history',  [ClientImportController::class, 'history'])->name('history');
            Route::get('/{log}',    [ClientImportController::class, 'show'])->name('show');
        });

        Route::prefix('export')->name('export.')->group(function () {
            Route::get('/',          [ClientExportController::class, 'download'])->name('download');
            Route::post('/schedule', [ClientExportController::class, 'scheduleExport'])->name('schedule');
        });

        // ==================== الحقول المخصصة ====================

        Route::prefix('custom-fields')->name('custom-fields.')->group(function () {
            Route::get('/',            [ClientCustomFieldController::class, 'index'])->name('index');
            Route::post('/',           [ClientCustomFieldController::class, 'store'])->name('store');
            Route::put('/{field}',     [ClientCustomFieldController::class, 'update'])->name('update');
            Route::delete('/{field}',  [ClientCustomFieldController::class, 'destroy'])->name('destroy');
            Route::post('/reorder',    [ClientCustomFieldController::class, 'reorder'])->name('reorder');
        });

        // ==================== قواعد الأتمتة ====================

        Route::prefix('automation-rules')->name('automation-rules.')->group(function () {
            Route::get('/',                         [AutomationRuleController::class, 'index'])->name('index');
            Route::post('/',                        [AutomationRuleController::class, 'store'])->name('store');
            Route::put('/{automationRule}',         [AutomationRuleController::class, 'update'])->name('update');
            Route::post('/{automationRule}/toggle', [AutomationRuleController::class, 'toggle'])->name('toggle');
            Route::delete('/{automationRule}',      [AutomationRuleController::class, 'destroy'])->name('destroy');
        });

        // ── 2. المسارات ذات {client} wildcard ────────────────────────────
        //    تأتي بعد كل الـ prefix الثابتة لتجنب التعارض

        Route::get('/{client}',      [ClientController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('/{client}',      [ClientController::class, 'update'])->name('update');
        Route::delete('/{client}',   [ClientController::class, 'destroy'])->name('destroy');

        // Bulk Actions
        Route::post('/bulk-action', [ClientController::class, 'bulkAction'])->name('bulk-action');

        // إجراءات إضافية
        Route::post('/{client}/archive',  [ClientController::class, 'archive'])->name('archive');
        Route::post('/{client}/restore',  [ClientController::class, 'restore'])->name('restore');
        Route::get('/{client}/timeline',  [ClientController::class, 'timeline'])->name('timeline');
        Route::get('/{client}/stats',     [ClientController::class, 'stats'])->name('stats');

        // وسوم عميل محدد
        Route::post('/{client}/tags/{tag}/assign', [ClientTagController::class, 'assign'])->name('tags.assign');
        Route::post('/{client}/tags/{tag}/remove', [ClientTagController::class, 'remove'])->name('tags.remove');
        Route::get('/{client}/tags/suggest',       [ClientTagController::class, 'suggest'])->name('tags.suggest');

        // متابعات عميل محدد
        Route::prefix('/{client}/follow-ups')->name('client-follow-ups.')->group(function () {
            Route::post('/',                    [ClientFollowUpController::class, 'store'])->name('store');
            Route::post('/{followUp}/complete', [ClientFollowUpController::class, 'complete'])->name('complete');
            Route::post('/{followUp}/cancel',   [ClientFollowUpController::class, 'cancel'])->name('cancel');
        });

        // حقول مخصصة لعميل محدد
        Route::post('/{client}/fields/{field}', [ClientCustomFieldController::class, 'saveValue'])->name('fields.save');

        // ==================== رموز البوابة ====================

        Route::prefix('/{client}/portal-tokens')->name('portal-tokens.')->group(function () {
            Route::get('/',           [ClientPortalTokenController::class, 'index'])->name('index');
            Route::post('/',          [ClientPortalTokenController::class, 'store'])->name('store');
            Route::delete('/{token}', [ClientPortalTokenController::class, 'destroy'])->name('destroy');
        });
    });
});
