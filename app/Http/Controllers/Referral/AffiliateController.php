<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\JoinAffiliateRequest;
use App\Http\Requests\Referral\RequestPayoutRequest;
use App\Modules\Referral\Actions\Payout\CreatePayoutRequestAction;
use App\Modules\Referral\DTOs\CreateAffiliateDTO;
use App\Modules\Referral\Enums\PayoutMethod;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function __construct(
        private readonly ReferralService $referralService,
    ) {}

    // ── Join ─────────────────────────────────────────────────────────────

    /**
     * عرض صفحة الانضمام لبرنامج الشركاء
     * إذا كان المستخدم مسوّقاً بالفعل → يُحوَّل للـ dashboard
     */
    public function join(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->affiliate) {
            return redirect()->route('affiliates.dashboard');
        }

        return view('referral.join', [
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

    /**
     * تسجيل طلب الانضمام لبرنامج الشركاء
     */
    public function store(JoinAffiliateRequest $request): RedirectResponse
    {
        $user = auth()->user();

        // منع التسجيل المزدوج
        if ($user->affiliate) {
            return redirect()->route('affiliates.dashboard');
        }

        $dto = new CreateAffiliateDTO(
            name:     $request->name,
            email:    $request->email,
            userId:   $user->id,
            whatsapp: $request->whatsapp ?: null,
        );

        Affiliate::create($dto->toArray());

        return redirect()
            ->route('affiliates.dashboard')
            ->with('success', 'تم تقديم طلبك بنجاح! سنراجعه خلال 1-3 أيام عمل وسنتواصل معك.');
    }

    // ── Dashboard ────────────────────────────────────────────────────────

    /**
     * لوحة تحكم المسوّق
     * إذا لم يكن المستخدم مسوّقاً → يُحوَّل لصفحة الانضمام
     */
    public function dashboard(): View|RedirectResponse
    {
        $affiliate = auth()->user()->affiliate;

        if (! $affiliate) {
            return redirect()->route('affiliates.join');
        }

        $referralUrl = $affiliate->display_code
            ? route('referral.track', ['identifier' => $affiliate->display_code])
            : route('referral.track', ['identifier' => $affiliate->id]);

        return view('referral.dashboard', [
            'affiliate'   => $affiliate,
            'referralUrl' => $referralUrl,
            'canPayout'   => $affiliate->isActive()
                             && $this->referralService->canRequestPayout($affiliate),
        ]);
    }

    // ── Commissions ──────────────────────────────────────────────────────

    public function commissions(): View|RedirectResponse
    {
        $affiliate = auth()->user()->affiliate;

        if (! $affiliate) {
            return redirect()->route('affiliates.join');
        }

        $commissions = $affiliate->commissions()
            ->latest('created_at')
            ->paginate(20);

        return view('referral.commissions', compact('affiliate', 'commissions'));
    }

    // ── Payouts ──────────────────────────────────────────────────────────

    public function payouts(): View|RedirectResponse
    {
        $affiliate = auth()->user()->affiliate;

        if (! $affiliate) {
            return redirect()->route('affiliates.join');
        }

        $payouts   = $affiliate->payouts()->latest('requested_at')->paginate(20);
        $canPayout = $affiliate->isActive()
                     && $this->referralService->canRequestPayout($affiliate);

        return view('referral.payouts', [
            'affiliate'     => $affiliate,
            'payouts'       => $payouts,
            'canPayout'     => $canPayout,
            'payoutMethods' => PayoutMethod::cases(),
        ]);
    }

    /**
     * تقديم طلب صرف الرصيد
     */
    public function requestPayout(RequestPayoutRequest $request): RedirectResponse
    {
        $affiliate = auth()->user()->affiliate;

        if (! $affiliate || ! $affiliate->isActive()) {
            return redirect()->route('affiliates.payouts')
                ->with('error', 'حسابك غير نشط.');
        }

        try {
            app(CreatePayoutRequestAction::class)->execute(
                affiliate: $affiliate,
                method:    $request->validated('method'),
                notes:     $request->validated('notes'),
            );

            return redirect()
                ->route('affiliates.payouts')
                ->with('success', 'تم تقديم طلب الصرف بنجاح! سنعالجه خلال 3-5 أيام عمل.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('affiliates.payouts')
                ->with('error', $e->getMessage());
        }
    }
}
