<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DebtApiController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\TransactionApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workuflow REST API  — v1
|--------------------------------------------------------------------------
| المصادقة: Laravel Sanctum (Bearer Token)
| التثبيت: composer require laravel/sanctum
|          php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
|          php artisan migrate
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ==================== Auth (Public) ====================
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login',      [AuthApiController::class, 'login'])->name('login');
    });

    // ==================== Protected Routes ====================
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::get('me',           [AuthApiController::class, 'me'])->name('me');
            Route::post('logout',      [AuthApiController::class, 'logout'])->name('logout');
            Route::post('logout-all',  [AuthApiController::class, 'logoutAll'])->name('logout-all');
        });

        // Projects
        Route::apiResource('projects', ProjectApiController::class)
            ->names('projects');

        // Transactions
        Route::apiResource('transactions', TransactionApiController::class)
            ->names('transactions');

        // Debts
        Route::apiResource('debts', DebtApiController::class)
            ->only(['index', 'store', 'show', 'destroy'])
            ->names('debts');
        Route::post('debts/{debt}/record-payment', [DebtApiController::class, 'recordPayment'])
            ->name('debts.record-payment');
        Route::post('debts/{debt}/mark-paid',      [DebtApiController::class, 'markAsPaid'])
            ->name('debts.mark-paid');
    });
});
