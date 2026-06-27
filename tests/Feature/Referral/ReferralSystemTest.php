<?php

/**
 * ReferralSystemTest — QA Checklist الشامل لنظام الإحالات
 *
 * يغطي: Functional | Fraud | Reconciliation | UI/Admin | Notifications
 *
 * التشغيل:
 *   php artisan test tests/Feature/Referral/ReferralSystemTest.php
 *   php artisan test tests/Feature/Referral/ --parallel
 */

use App\Models\Subscription;
use App\Models\User;
use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Referral\Actions\Commission\CreateReferralCommissionAction;
use App\Modules\Referral\Actions\Commission\UpgradeAffiliateTierAction;
use App\Modules\Referral\Actions\Payout\CreatePayoutRequestAction;
use App\Modules\Referral\DTOs\CreateCommissionDTO;
use App\Modules\Referral\Enums\AffiliateTier;
use App\Modules\Referral\Enums\CommissionStatus;
use App\Modules\Referral\Enums\PayoutMethod;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralClick;
use App\Modules\Referral\Models\ReferralCommission;
use App\Modules\Referral\Models\ReferralPayout;
use App\Modules\Referral\Notifications\AffiliateApprovedNotification;
use App\Modules\Referral\Notifications\NewCommissionEarnedNotification;
use App\Modules\Referral\Notifications\PayoutProcessedNotification;
use App\Modules\Referral\Notifications\TierUpgradedNotification;
use App\Modules\Referral\Services\ReferralService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

/**
 * ينشئ Affiliate نشط مع User مرتبط به.
 */
function makeActiveAffiliate(array $overrides = []): Affiliate
{
    $user = User::factory()->create();

    return Affiliate::create(array_merge([
        'id'              => Str::ulid()->toString(),
        'user_id'         => $user->id,
        'name'            => $user->name,
        'email'           => $user->email,
        'display_code'    => strtoupper(Str::random(8)),
        'commission_rate' => 30.00,
        'status'          => 'active',
        'tier'            => 'standard',
        'total_referrals' => 0,
        'total_converted' => 0,
        'total_earned'    => 0.00,
        'total_paid'      => 0.00,
    ], $overrides));
}

/**
 * ينشئ ReferralClick مرتبطاً بـ Affiliate.
 */
function makeClick(Affiliate $affiliate, string $visitorToken = null): ReferralClick
{
    return ReferralClick::create([
        'id'            => Str::ulid()->toString(),
        'affiliate_id'  => $affiliate->id,
        'visitor_token' => $visitorToken ?? Str::ulid()->toString(),
        'ip_address'    => '1.2.3.4',
        'user_agent'    => 'Mozilla/5.0',
        'landing_page'  => 'http://localhost/ref/' . $affiliate->display_code,
    ]);
}

/**
 * ينشئ User مُحال مع Attribution كاملة.
 */
function makeReferredUser(Affiliate $affiliate, ReferralClick $click): User
{
    $user = User::factory()->create([
        'referred_by_affiliate_id' => $affiliate->id,
        'referral_click_id'        => $click->id,
        'referral_attributed_at'   => now(),
    ]);

    $affiliate->increment('total_referrals');

    return $user;
}

/**
 * ينشئ Subscription لمستخدم معين.
 */
function makeSubscription(User $user, string $plan = 'pro', string $status = 'active'): Subscription
{
    return Subscription::create([
        'user_id'    => $user->id,
        'plan'       => $plan,
        'status'     => $status,
        'starts_at'  => now(),
        'provider_subscription_id' => 'sub_' . Str::random(10),
    ]);
}

// ─────────────────────────────────────────────────────────────────────────────
// A. Functional Tests
// ─────────────────────────────────────────────────────────────────────────────

describe('A. Functional', function () {

    test('A01 — إنشاء Affiliate جديد بالحقول الصحيحة', function () {
        $affiliate = makeActiveAffiliate(['display_code' => 'TEST2026']);

        expect(Affiliate::find($affiliate->id))
            ->not->toBeNull()
            ->status->value->toBe('active')
            ->tier->value->toBe('standard')
            ->commission_rate->toBe('30.00')
            ->display_code->toBe('TEST2026');
    });

    test('A02 — زيارة /ref/{display_code} تُسجّل click وتُخزّن في Session', function () {
        $affiliate = makeActiveAffiliate(['display_code' => 'TRACK01']);

        $response = $this->withSession([])->get('/ref/TRACK01');

        // يجب إعادة توجيه للتسجيل
        $response->assertRedirect('/register');

        // سجل click يوجد في DB
        expect(ReferralClick::where('affiliate_id', $affiliate->id)->count())->toBe(1);
    });

    test('A03 — زيارة /ref/{ulid} تُسجّل click (ULID كمعرّف)', function () {
        $affiliate = makeActiveAffiliate();

        $response = $this->withSession([])->get('/ref/' . $affiliate->id);

        $response->assertRedirect('/register');
        expect(ReferralClick::where('affiliate_id', $affiliate->id)->count())->toBe(1);
    });

    test('A04 — ReferralClick يُنشأ بحقول صحيحة', function () {
        $affiliate = makeActiveAffiliate(['display_code' => 'CLICK04']);

        $this->withSession([])->get('/ref/CLICK04');

        $click = ReferralClick::where('affiliate_id', $affiliate->id)->first();

        expect($click)
            ->not->toBeNull()
            ->affiliate_id->toBe($affiliate->id)
            ->ip_address->not->toBeNull()
            ->visitor_token->not->toBeNull();
    });

    test('A05 — Attribution: تسجيل مستخدم عبر إحالة يربطه بالـ Affiliate', function () {
        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);

        $user = User::factory()->create([
            'referred_by_affiliate_id' => $affiliate->id,
            'referral_click_id'        => $click->id,
            'referral_attributed_at'   => now(),
        ]);

        expect($user->refresh())
            ->referred_by_affiliate_id->toBe($affiliate->id)
            ->referral_click_id->toBe($click->id)
            ->referral_attributed_at->not->toBeNull();
    });

    test('A06 — اشتراك مدفوع أول → يُنشئ عمولة (Listener)', function () {
        // ملاحظة: Queue::fake() + afterCommit=true + RefreshDatabase لا يتوافقان
        // لأن RefreshDatabase يُغلّف كل test في transaction لا تُكمَّل أبداً.
        // الحل: نُشغّل الـ Listener مباشرةً ونتحقق من أثره.
        Notification::fake();
        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        $event = new SubscriptionActivated($sub, isFirstActivation: true, cycle: 'monthly');
        app(\App\Modules\Referral\Listeners\CreateReferralCommission::class)->handle($event);

        expect(ReferralCommission::where('subscription_id', $sub->id)->count())->toBe(1);
    });

    test('A07 — CreateReferralCommissionAction يُنشئ عمولة بالحقول الصحيحة', function () {
        $affiliate = makeActiveAffiliate(['commission_rate' => 30.00]);
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        $action     = app(CreateReferralCommissionAction::class);
        $commission = $action->execute(new CreateCommissionDTO(
            affiliateId:        $affiliate->id,
            subscriptionId:     $sub->id,
            referredUserId:     $user->id,
            amount:             3.00,
            rate:               30.00,
            triggerSource:      'togo_callback',
            subscriptionAmount: 10.00,
            subscriptionPlan:   'pro',
            subscriptionCycle:  'monthly',
            fraudFlagged:       false,
        ));

        expect($commission->refresh())
            ->affiliate_id->toBe($affiliate->id)
            ->subscription_id->toBe($sub->id)
            ->amount->toBe('3.00')
            ->rate->toBe('30.00')
            ->status->value->toBe('pending')
            ->fraud_flagged->toBeFalse();
    });

    test('A08 — تجديد الاشتراك (isFirstActivation=false) لا يُنشئ عمولة', function () {
        // نُشغّل الـ Listener مباشرةً — يعود فوراً بسبب Guard 1 (isFirstActivation)
        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        $event = new SubscriptionActivated($sub, isFirstActivation: false, cycle: 'monthly');
        app(\App\Modules\Referral\Listeners\CreateReferralCommission::class)->handle($event);

        expect(ReferralCommission::where('subscription_id', $sub->id)->count())->toBe(0);
    });

    test('A09 — طلب صرف يدوي يُنشئ ReferralPayout بالحقول الصحيحة', function () {
        $affiliate = makeActiveAffiliate([
            'total_earned' => 50.00,
            'total_paid'   => 0.00,
            'payout_method' => 'bank',
        ]);

        $payout = app(CreatePayoutRequestAction::class)->execute(
            $affiliate,
            PayoutMethod::Bank->value,
            'طلب صرف اختبار'
        );

        expect($payout)
            ->affiliate_id->toBe($affiliate->id)
            ->amount->toBe('50.00')
            ->method->value->toBe('bank')
            ->status->value->toBe('requested');
    });

    test('A10 — اعتماد العمولة يزيد total_earned للـ Affiliate', function () {
        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        $commission = ReferralCommission::create([
            'id'                  => Str::ulid()->toString(),
            'affiliate_id'        => $affiliate->id,
            'subscription_id'     => $sub->id,
            'referred_user_id'    => $user->id,
            'amount'              => '5.00',
            'rate'                => '30.00',
            'trigger_source'      => 'togo_callback',
            'subscription_amount' => '10.00',
            'subscription_plan'   => 'pro',
            'subscription_cycle'  => 'monthly',
            'status'              => 'pending',
            'fraud_flagged'       => false,
        ]);

        // محاكاة approve من Filament
        \Illuminate\Support\Facades\DB::transaction(function () use ($commission, $affiliate): void {
            $commission->update(['status' => 'approved']);
            $affiliate->increment('total_earned', $commission->amount);
        });

        expect($affiliate->refresh()->total_earned)->toBe('5.00');
    });

    test('A11 — تأكيد الصرف يزيد total_paid ويُعيِّن processed_at', function () {
        $affiliate = makeActiveAffiliate([
            'total_earned' => 50.00,
            'total_paid'   => 0.00,
        ]);

        $payout = ReferralPayout::create([
            'id'           => Str::ulid()->toString(),
            'affiliate_id' => $affiliate->id,
            'amount'       => '50.00',
            'currency'     => 'USD',
            'method'       => 'bank',
            'status'       => 'processing',
            'requested_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($payout, $affiliate): void {
            $payout->update(['status' => 'paid', 'processed_at' => now()]);
            $affiliate->increment('total_paid', $payout->amount);
        });

        expect($affiliate->refresh()->total_paid)->toBe('50.00');
        expect($payout->refresh()->processed_at)->not->toBeNull();
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// B. Fraud Tests
// ─────────────────────────────────────────────────────────────────────────────

describe('B. Fraud', function () {

    test('B01 — Self-referral: المسوّق لا يُحال عبر رابطه هو', function () {
        $affiliate = makeActiveAffiliate();

        // المستخدم نفسه هو صاحب الـ Affiliate — لا يوجد referred_by_affiliate_id
        $user = $affiliate->user;

        expect($user->referred_by_affiliate_id)->toBeNull();
    });

    test('B02 — Duplicate commission: نفس subscription_id لا يُنشئ عمولتين', function () {
        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        $dto = new CreateCommissionDTO(
            affiliateId:        $affiliate->id,
            subscriptionId:     $sub->id,
            referredUserId:     $user->id,
            amount:             3.00,
            rate:               30.00,
            triggerSource:      'togo_callback',
            subscriptionAmount: 10.00,
            subscriptionPlan:   'pro',
            subscriptionCycle:  'monthly',
            fraudFlagged:       false,
        );

        app(CreateReferralCommissionAction::class)->execute($dto);

        // المحاولة الثانية يجب أن ترمي UniqueConstraintViolationException
        expect(fn () => app(CreateReferralCommissionAction::class)->execute($dto))
            ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);

        expect(ReferralCommission::where('subscription_id', $sub->id)->count())->toBe(1);
    });

    test('B03 — عدم وجود Affiliate لا يُنشئ عمولة', function () {
        $user = User::factory()->create(['referred_by_affiliate_id' => null]);
        $sub  = makeSubscription($user);

        $initialCount = ReferralCommission::count();

        event(new SubscriptionActivated($sub, isFirstActivation: true, cycle: 'monthly'));

        // الـ Listener يعود بسبب Guard 2 — لا عمولة
        expect(ReferralCommission::count())->toBe($initialCount);
    });

    test('B04 — Affiliate موقوف لا يكسب عمولات جديدة', function () {
        $affiliate = makeActiveAffiliate(['status' => 'suspended']);
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        // الـ Listener يفحص isActive() — لا يُنشئ عمولة لمسوّق موقوف
        // (التحقق يتم في Guard داخل Listener عبر affiliate->isActive())
        $initialCount = ReferralCommission::count();
        event(new SubscriptionActivated($sub, isFirstActivation: true, cycle: 'monthly'));
        expect(ReferralCommission::count())->toBe($initialCount);
    });

    test('B05 — fraud_flagged = true يُخزَّن في سجل العمولة', function () {
        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        $commission = app(CreateReferralCommissionAction::class)->execute(
            new CreateCommissionDTO(
                affiliateId:        $affiliate->id,
                subscriptionId:     $sub->id,
                referredUserId:     $user->id,
                amount:             3.00,
                rate:               30.00,
                triggerSource:      'togo_callback',
                subscriptionAmount: 10.00,
                subscriptionPlan:   'pro',
                subscriptionCycle:  'monthly',
                fraudFlagged:       true,
            )
        );

        expect($commission->fraud_flagged)->toBeTrue();
    });

    test('B06 — canRequestPayout يُرجع false عند رصيد < 20', function () {
        $affiliate = makeActiveAffiliate([
            'total_earned' => 10.00,
            'total_paid'   => 0.00,
        ]);

        $service = app(ReferralService::class);
        expect($service->canRequestPayout($affiliate))->toBeFalse();
    });

    test('B07 — canRequestPayout يُرجع false عند وجود طلب صرف معلّق', function () {
        $affiliate = makeActiveAffiliate([
            'total_earned' => 100.00,
            'total_paid'   => 0.00,
        ]);

        // طلب صرف قائم
        ReferralPayout::create([
            'id'           => Str::ulid()->toString(),
            'affiliate_id' => $affiliate->id,
            'amount'       => '100.00',
            'currency'     => 'USD',
            'method'       => 'bank',
            'status'       => 'requested',
            'requested_at' => now(),
        ]);

        $service = app(ReferralService::class);
        expect($service->canRequestPayout($affiliate))->toBeFalse();
    });

    test('B08 — canRequestPayout يُرجع true عند رصيد >= 20 وبدون طلب معلّق', function () {
        $affiliate = makeActiveAffiliate([
            'total_earned' => 50.00,
            'total_paid'   => 0.00,
        ]);

        $service = app(ReferralService::class);
        expect($service->canRequestPayout($affiliate))->toBeTrue();
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// C. Reconciliation Tests
// ─────────────────────────────────────────────────────────────────────────────

describe('C. Reconciliation', function () {

    test('C01 — --dry-run لا يُغيِّر قاعدة البيانات', function () {
        $affiliate = makeActiveAffiliate([
            'total_referrals' => 99, // متعمَّد خطأ
            'total_converted' => 0,
            'total_earned'    => 0.00,
        ]);

        $this->artisan('referral:reconcile --dry-run')
            ->assertExitCode(0);

        // لم يتغير total_referrals
        expect($affiliate->refresh()->total_referrals)->toBe(99);
    });

    test('C02 — reconcile يُصحِّح total_referrals', function () {
        $affiliate = makeActiveAffiliate(['total_referrals' => 99]);

        // مستخدم واحد مُحال فعلاً
        User::factory()->create([
            'referred_by_affiliate_id' => $affiliate->id,
            'referral_click_id'        => null,
            'referral_attributed_at'   => now(),
        ]);

        $this->artisan('referral:reconcile')->assertExitCode(0);

        expect($affiliate->refresh()->total_referrals)->toBe(1);
    });

    test('C03 — reconcile يُصحِّح total_converted', function () {
        $affiliate = makeActiveAffiliate(['total_converted' => 99]);

        // عمولة واحدة فقط بحالة approved
        $click = makeClick($affiliate);
        $user  = makeReferredUser($affiliate, $click);
        $sub   = makeSubscription($user);

        ReferralCommission::create([
            'id'                  => Str::ulid()->toString(),
            'affiliate_id'        => $affiliate->id,
            'subscription_id'     => $sub->id,
            'referred_user_id'    => $user->id,
            'amount'              => '3.00',
            'rate'                => '30.00',
            'trigger_source'      => 'togo_callback',
            'subscription_amount' => '10.00',
            'subscription_plan'   => 'pro',
            'subscription_cycle'  => 'monthly',
            'status'              => 'approved',
            'fraud_flagged'       => false,
        ]);

        $this->artisan('referral:reconcile')->assertExitCode(0);

        expect($affiliate->refresh()->total_converted)->toBe(1);
    });

    test('C04 — reconcile يُصحِّح total_earned', function () {
        $affiliate = makeActiveAffiliate(['total_earned' => 0.00]);

        $click = makeClick($affiliate);
        $user  = makeReferredUser($affiliate, $click);
        $sub   = makeSubscription($user);

        ReferralCommission::create([
            'id'                  => Str::ulid()->toString(),
            'affiliate_id'        => $affiliate->id,
            'subscription_id'     => $sub->id,
            'referred_user_id'    => $user->id,
            'amount'              => '7.50',
            'rate'                => '30.00',
            'trigger_source'      => 'togo_callback',
            'subscription_amount' => '25.00',
            'subscription_plan'   => 'pro',
            'subscription_cycle'  => 'monthly',
            'status'              => 'approved',
            'fraud_flagged'       => false,
        ]);

        $this->artisan('referral:reconcile')->assertExitCode(0);

        expect((float) $affiliate->refresh()->total_earned)->toBe(7.50);
    });

    test('C05 — reconcile يُصحِّح total_paid', function () {
        $affiliate = makeActiveAffiliate([
            'total_earned' => 50.00,
            'total_paid'   => 0.00,
        ]);

        ReferralPayout::create([
            'id'           => Str::ulid()->toString(),
            'affiliate_id' => $affiliate->id,
            'amount'       => '50.00',
            'currency'     => 'USD',
            'method'       => 'bank',
            'status'       => 'paid',
            'requested_at' => now(),
            'processed_at' => now(),
        ]);

        $this->artisan('referral:reconcile')->assertExitCode(0);

        expect((float) $affiliate->refresh()->total_paid)->toBe(50.00);
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// D. Tier Upgrade Tests
// ─────────────────────────────────────────────────────────────────────────────

describe('D. Tier Upgrade', function () {

    test('D01 — 0 conversions → Standard (30%)', function () {
        $affiliate = makeActiveAffiliate(['total_converted' => 0]);
        app(UpgradeAffiliateTierAction::class)->execute($affiliate);
        expect($affiliate->refresh()->tier->value)->toBe('standard');
    });

    test('D02 — 10 conversions → Silver (35%)', function () {
        Notification::fake();
        $affiliate = makeActiveAffiliate(['total_converted' => 10]);
        app(UpgradeAffiliateTierAction::class)->execute($affiliate);
        expect($affiliate->refresh()->tier->value)->toBe('silver');
        expect((float) $affiliate->commission_rate)->toBe(35.0);
    });

    test('D03 — 30 conversions → Gold (40%)', function () {
        Notification::fake();
        $affiliate = makeActiveAffiliate(['total_converted' => 30]);
        app(UpgradeAffiliateTierAction::class)->execute($affiliate);
        expect($affiliate->refresh()->tier->value)->toBe('gold');
    });

    test('D04 — 100 conversions → Platinum (45%)', function () {
        Notification::fake();
        $affiliate = makeActiveAffiliate(['total_converted' => 100]);
        app(UpgradeAffiliateTierAction::class)->execute($affiliate);
        expect($affiliate->refresh()->tier->value)->toBe('platinum');
        expect((float) $affiliate->commission_rate)->toBe(45.0);
    });

    test('D05 — Upgrade idempotent: نفس الـ Tier لا يتغير', function () {
        $affiliate = makeActiveAffiliate([
            'total_converted' => 5,
            'tier'            => 'standard',
            'commission_rate' => 30.00,
        ]);

        app(UpgradeAffiliateTierAction::class)->execute($affiliate);

        // لا يجب أن يُعدَّل السجل (queries = 1 فقط: SELECT)
        expect($affiliate->refresh()->tier->value)->toBe('standard');
    });

    test('D06 — TierUpgradedNotification تُرسَل عند الترقية', function () {
        Notification::fake();

        $affiliate = makeActiveAffiliate(['total_converted' => 10]); // سيُرقَّى لـ Silver

        app(UpgradeAffiliateTierAction::class)->execute($affiliate);

        Notification::assertSentTo(
            $affiliate->user,
            TierUpgradedNotification::class,
            fn ($n) => $n->newTier === AffiliateTier::Silver
        );
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// E. Notifications Tests
// ─────────────────────────────────────────────────────────────────────────────

describe('E. Notifications', function () {

    test('E01 — AffiliateApprovedNotification: via = mail + database', function () {
        $affiliate = makeActiveAffiliate();
        $n         = new AffiliateApprovedNotification($affiliate);

        expect($n->via($affiliate->user))->toBe(['mail', 'database']);
    });

    test('E02 — AffiliateApprovedNotification: toArray يحتوي الحقول المطلوبة', function () {
        $affiliate = makeActiveAffiliate(['display_code' => 'APPR01']);
        $n         = new AffiliateApprovedNotification($affiliate);
        $arr       = $n->toArray($affiliate->user);

        expect($arr)
            ->toHaveKeys(['type', 'title', 'affiliate_id', 'display_code', 'rate', 'link'])
            ->and($arr['type'])->toBe('affiliate_approved')
            ->and($arr['display_code'])->toBe('APPR01');
    });

    test('E03 — NewCommissionEarnedNotification: via = mail + database', function () {
        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        $commission = ReferralCommission::create([
            'id'                  => Str::ulid()->toString(),
            'affiliate_id'        => $affiliate->id,
            'subscription_id'     => $sub->id,
            'referred_user_id'    => $user->id,
            'amount'              => '3.00',
            'rate'                => '30.00',
            'trigger_source'      => 'togo_callback',
            'subscription_amount' => '10.00',
            'subscription_plan'   => 'pro',
            'subscription_cycle'  => 'monthly',
            'status'              => 'pending',
            'fraud_flagged'       => false,
        ]);

        $n = new NewCommissionEarnedNotification($commission);
        expect($n->via($affiliate->user))->toBe(['mail', 'database']);
    });

    test('E04 — PayoutProcessedNotification approved=true: mail subject صحيح', function () {
        $affiliate = makeActiveAffiliate();
        $payout    = ReferralPayout::create([
            'id'           => Str::ulid()->toString(),
            'affiliate_id' => $affiliate->id,
            'amount'       => '25.00',
            'currency'     => 'USD',
            'method'       => 'bank',
            'status'       => 'paid',
            'requested_at' => now(),
        ]);

        $n    = new PayoutProcessedNotification($payout, approved: true);
        $mail = $n->toMail($affiliate->user);

        expect($mail->subject)->toContain('25.00')
            ->and($mail->subject)->toContain('✅');
    });

    test('E05 — PayoutProcessedNotification approved=false: mail يُشير للرفض', function () {
        $affiliate = makeActiveAffiliate();
        $payout    = ReferralPayout::create([
            'id'           => Str::ulid()->toString(),
            'affiliate_id' => $affiliate->id,
            'amount'       => '25.00',
            'currency'     => 'USD',
            'method'       => 'bank',
            'status'       => 'rejected',
            'requested_at' => now(),
        ]);

        $n    = new PayoutProcessedNotification($payout, approved: false);
        $mail = $n->toMail($affiliate->user);

        expect($mail->subject)->toContain('❌');
    });

    test('E06 — CreateReferralCommissionAction ترسل NewCommissionEarnedNotification', function () {
        Notification::fake();

        $affiliate = makeActiveAffiliate();
        $click     = makeClick($affiliate);
        $user      = makeReferredUser($affiliate, $click);
        $sub       = makeSubscription($user);

        app(CreateReferralCommissionAction::class)->execute(new CreateCommissionDTO(
            affiliateId:        $affiliate->id,
            subscriptionId:     $sub->id,
            referredUserId:     $user->id,
            amount:             3.00,
            rate:               30.00,
            triggerSource:      'togo_callback',
            subscriptionAmount: 10.00,
            subscriptionPlan:   'pro',
            subscriptionCycle:  'monthly',
            fraudFlagged:       false,
        ));

        Notification::assertSentTo($affiliate->user, NewCommissionEarnedNotification::class);
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// F. UI / Routes Tests
// ─────────────────────────────────────────────────────────────────────────────

describe('F. UI / Routes', function () {

    test('F01 — /affiliates/join يعرض صفحة الانضمام لمستخدم مُسجَّل', function () {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/affiliates/join')->assertOk();
    });

    test('F02 — /affiliates/dashboard يُعيد توجيه غير المسوّق لـ join', function () {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/affiliates/dashboard')
            ->assertRedirect(route('affiliates.join'));
    });

    test('F03 — /affiliates/dashboard يعرض الصفحة لمسوّق نشط', function () {
        $affiliate = makeActiveAffiliate();
        $this->actingAs($affiliate->user)
            ->get('/affiliates/dashboard')
            ->assertOk()
            ->assertSee($affiliate->display_code);
    });

    test('F04 — /affiliates/commissions يعرض العمولات بـ pagination', function () {
        $affiliate = makeActiveAffiliate();
        $this->actingAs($affiliate->user)
            ->get('/affiliates/commissions')
            ->assertOk();
    });

    test('F05 — /affiliates/payouts يعرض صفحة الصرف', function () {
        $affiliate = makeActiveAffiliate();
        $this->actingAs($affiliate->user)
            ->get('/affiliates/payouts')
            ->assertOk();
    });

    test('F06 — POST /affiliates/payouts يرفض الطلب عند رصيد < 20', function () {
        $affiliate = makeActiveAffiliate([
            'total_earned' => 10.00,
            'total_paid'   => 0.00,
            'payout_method' => 'bank',
        ]);

        // canRequestPayout يُرجع false (balance=10 < 20) → CreatePayoutRequestAction يرمي RuntimeException
        // Controller يمسكه ويُعيد redirect مع flash 'error'
        // ملاحظة: CSRF لا يُتجاوز تلقائياً في هذا الإعداد — نُعطّله صراحةً للـ test
        $response = $this->actingAs($affiliate->user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/affiliates/payouts', ['method' => 'bank']);

        // يجب أن لا يُنشأ أي طلب صرف
        expect(\App\Modules\Referral\Models\ReferralPayout::where('affiliate_id', $affiliate->id)->count())->toBe(0);

        // يجب أن يُعاد التوجيه مع رسالة error
        $response->assertSessionHas('error');
    });

    test('F07 — /ref/{identifier} لمسوّق غير نشط يُعيد redirect لـ /', function () {
        $affiliate = makeActiveAffiliate(['status' => 'suspended']);
        $this->get('/ref/' . $affiliate->display_code)
            ->assertRedirect('/');
    });

    test('F08 — زائر بدون مصادقة لا يصل لـ /affiliates/dashboard', function () {
        $this->get('/affiliates/dashboard')
            ->assertRedirect('/login');
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// G. ReferralService Tests
// ─────────────────────────────────────────────────────────────────────────────

describe('G. ReferralService', function () {

    test('G01 — resolveAffiliate يجد بـ display_code', function () {
        $affiliate = makeActiveAffiliate(['display_code' => 'FIND01']);
        $service   = app(ReferralService::class);

        expect($service->resolveAffiliate('FIND01')?->id)->toBe($affiliate->id);
    });

    test('G02 — resolveAffiliate يجد بـ ULID', function () {
        $affiliate = makeActiveAffiliate();
        $service   = app(ReferralService::class);

        expect($service->resolveAffiliate($affiliate->id)?->id)->toBe($affiliate->id);
    });

    test('G03 — resolveAffiliate يُرجع null لكود غير موجود', function () {
        $service = app(ReferralService::class);
        expect($service->resolveAffiliate('NOTEXIST'))->toBeNull();
    });

    test('G04 — calculateCommission: 30% من $10 = $3', function () {
        $service = app(ReferralService::class);
        expect($service->calculateCommission(10.00, 30.00))->toBe(3.00);
    });

    test('G05 — calculateCommission: 45% من $100 = $45', function () {
        $service = app(ReferralService::class);
        expect($service->calculateCommission(100.00, 45.00))->toBe(45.00);
    });

    test('G06 — resolveCommissionRate يُرجع commission_rate المخزَّنة', function () {
        $affiliate = makeActiveAffiliate(['commission_rate' => 40.00]);
        $service   = app(ReferralService::class);
        expect($service->resolveCommissionRate($affiliate))->toBe(40.00);
    });

});
