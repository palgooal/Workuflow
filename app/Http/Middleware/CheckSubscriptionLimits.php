<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يتحقق من حدود الاشتراك قبل السماح بالإنشاء
 * يُطبَّق على routes الـ store لـ projects و transactions
 */
class CheckSubscriptionLimits
{
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $plan = $user->currentPlan();

        match ($resource) {
            'projects' => $this->checkProjects($user, $plan),
            'transactions' => $this->checkTransactions($user, $plan),
            default => null,
        };

        return $next($request);
    }

    private function checkProjects($user, $plan): void
    {
        $max   = $plan->maxProjects();
        $count = $user->projects()->count();

        if ($count >= $max) {
            session()->flash('upgrade_prompt', [
                'resource' => 'projects',
                'message'  => "وصلت للحد الأقصى ({$max} مشاريع) في خطتك الحالية.",
                'hint'     => 'الترقية إلى Pro تتيح لك حتى 10 مشاريع.',
            ]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->withInput()
            );
        }
    }

    private function checkTransactions($user, $plan): void
    {
        $max   = $plan->maxTransactionsPerMonth();
        $count = $user->transactions()
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();

        if ($count >= $max) {
            session()->flash('upgrade_prompt', [
                'resource' => 'transactions',
                'message'  => "وصلت للحد الأقصى ({$max} معاملة) هذا الشهر في خطتك الحالية.",
                'hint'     => 'الترقية إلى Pro تتيح لك 500 معاملة شهرياً.',
            ]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->withInput()
            );
        }
    }
}
