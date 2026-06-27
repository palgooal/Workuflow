<?php

use App\Http\Controllers\Referral\AffiliateController;
use App\Http\Controllers\Referral\ReferralRedirectController;
use App\Http\Middleware\TrackReferralCode;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Referral Routes
|--------------------------------------------------------------------------
*/

// Referral Tracking (guest)
Route::get('/ref/{identifier}', ReferralRedirectController::class)
    ->middleware(TrackReferralCode::class)
    ->name('referral.track');

// Affiliate Dashboard (auth + verified)
Route::middleware(['auth', 'verified'])->prefix('affiliates')->name('affiliates.')->group(function () {
    Route::get('join',  [AffiliateController::class, 'join'])->name('join');
    Route::post('join', [AffiliateController::class, 'store'])->name('store');

    Route::get('dashboard',   [AffiliateController::class, 'dashboard'])->name('dashboard');
    Route::get('commissions', [AffiliateController::class, 'commissions'])->name('commissions');

    Route::get('payouts',  [AffiliateController::class, 'payouts'])->name('payouts');
    Route::post('payouts', [AffiliateController::class, 'requestPayout'])->name('payout.request');
});
