<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Projects — Phase 4
    Route::resource('projects', ProjectController::class);

    // Placeholder routes — ستُستبدل بـ Controllers حقيقية مع كل Phase
    Route::get('/transactions', fn() => 'transactions')->name('transactions.index');
    Route::get('/debts',        fn() => 'debts')->name('debts.index');
    Route::get('/budget',       fn() => 'budget')->name('budget.index');
    Route::get('/recurring',    fn() => 'recurring')->name('recurring.index');
    Route::get('/reports',      fn() => 'reports')->name('reports.index');
    Route::get('/categories',   fn() => 'categories')->name('categories.index');
    Route::get('/notifications',fn() => 'notifications')->name('notifications.index');
    Route::get('/settings',     fn() => 'settings')->name('settings.index');

    // Profile (Breeze)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
