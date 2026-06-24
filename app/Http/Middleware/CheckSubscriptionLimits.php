<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يتحقق من حدود الاشتراك قبل السماح بالإنشاء.
 * يُطبَّق على routes الـ store لكل مورد خاضع للحدود.
 *
 * الموارد المدعومة:
 *   projects     — حد إجمالي (غير شهري)
 *   transactions — حد شهري   (عمود: transaction_date)
 *   clients      — حد إجمالي (غير شهري)
 *   invoices     — حد شهري   (عمود: created_at)
 *   quotes       — حد شهري   (عمود: created_at)
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
            'projects'     => $this->checkProjects($user, $plan),
            'transactions' => $this->checkTransactions($user, $plan),
            'clients'      => $this->checkClients($user, $plan),
            'invoices'     => $this->checkInvoices($user, $plan),
            'quotes'       => $this->checkQuotes($user, $plan),
            default        => null,
        };

        return $next($request);
    }

    // ─────────────────────────────────────────────────────────────
    // Projects — حد إجمالي
    // ─────────────────────────────────────────────────────────────

    private function checkProjects($user, $plan): void
    {
        $max   = $plan->maxProjects();
        $count = $user->projects()->count();

        if ($count >= $max) {
            session()->flash('upgrade_prompt', [
                'resource' => 'projects',
                'message'  => "وصلت للحد الأقصى ({$max} مشاريع) في خطتك الحالية.",
                'hint'     => 'الترقية إلى Pro تتيح لك مشاريع غير محدودة.',
            ]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->withInput()
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Transactions — حد شهري
    // ─────────────────────────────────────────────────────────────

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
                'hint'     => 'الترقية إلى Pro تتيح لك 1,000 معاملة شهرياً.',
            ]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->withInput()
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Clients — حد إجمالي
    // Client يستخدم SoftDeletes — count() يستثني المحذوفين تلقائياً.
    // نستعلم مباشرة بدلاً من clients() على User (العلاقة غير موجودة).
    // ─────────────────────────────────────────────────────────────

    private function checkClients($user, $plan): void
    {
        $max   = $plan->maxClients();
        $count = Client::where('user_id', $user->id)->count();

        if ($count >= $max) {
            session()->flash('upgrade_prompt', [
                'resource' => 'clients',
                'message'  => "وصلت للحد الأقصى ({$max} عملاء) في خطتك الحالية.",
                'hint'     => 'الترقية إلى Pro تتيح لك عملاء غير محدودين.',
            ]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->withInput()
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Invoices — حد شهري
    // نعدّ فواتير الشهر الحالي فقط بـ created_at (لا يمكن تزويرها).
    // SoftDeletes يستثني المحذوفات تلقائياً.
    // ─────────────────────────────────────────────────────────────

    private function checkInvoices($user, $plan): void
    {
        $max   = $plan->maxInvoicesPerMonth();
        $count = Invoice::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($count >= $max) {
            session()->flash('upgrade_prompt', [
                'resource' => 'invoices',
                'message'  => "وصلت للحد الأقصى ({$max} فواتير) هذا الشهر في خطتك الحالية.",
                'hint'     => 'الترقية إلى Pro تتيح لك فواتير غير محدودة.',
            ]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->withInput()
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Quotes — حد شهري
    // نفس نهج Invoices.
    // ─────────────────────────────────────────────────────────────

    private function checkQuotes($user, $plan): void
    {
        $max   = $plan->maxQuotesPerMonth();
        $count = Quote::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($count >= $max) {
            session()->flash('upgrade_prompt', [
                'resource' => 'quotes',
                'message'  => "وصلت للحد الأقصى ({$max} عروض أسعار) هذا الشهر في خطتك الحالية.",
                'hint'     => 'الترقية إلى Pro تتيح لك عروض أسعار غير محدودة.',
            ]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->withInput()
            );
        }
    }
}
