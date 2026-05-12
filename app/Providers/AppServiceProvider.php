<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Debt;
use App\Models\Project;
use App\Models\Transaction;
use App\Policies\CategoryPolicy;
use App\Policies\DebtPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Debt::class, DebtPolicy::class);
    }
}
