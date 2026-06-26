<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Modules\Auth\Actions\RegisterUserAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $currencies = [
            'SAR' => 'ريال سعودي (SAR)',
            'USD' => 'دولار أمريكي (USD)',
            'EUR' => 'يورو (EUR)',
            'GBP' => 'جنيه إسترليني (GBP)',
            'AED' => 'درهم إماراتي (AED)',
            'KWD' => 'دينار كويتي (KWD)',
        ];

        $timezones = [
            'Asia/Riyadh'     => 'الرياض (توقيت السعودية)',
            'Asia/Dubai'      => 'دبي (توقيت الإمارات)',
            'Asia/Kuwait'     => 'الكويت',
            'Asia/Baghdad'    => 'بغداد (توقيت العراق)',
            'Asia/Jerusalem'  => 'القدس (توقيت فلسطين)',
            'Asia/Amman'      => 'عمّان (توقيت الأردن)',
            'Asia/Beirut'     => 'بيروت (توقيت لبنان)',
            'Asia/Damascus'   => 'دمشق (توقيت سوريا)',
            'Africa/Cairo'    => 'القاهرة (توقيت مصر)',
            'Africa/Tunis'    => 'تونس',
            'Africa/Algiers'  => 'الجزائر',
            'Africa/Casablanca' => 'الدار البيضاء (المغرب)',
            'Europe/London'   => 'لندن (GMT)',
            'America/New_York'=> 'نيويورك (EST)',
            'UTC'             => 'UTC',
        ];

        $formToken = encrypt(now()->timestamp);

        return view('auth.register', compact('currencies', 'timezones', 'formToken'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        app(RegisterUserAction::class)->execute($request);

        // ── توجيه ما بعد التسجيل ──────────────────────────────────────────
        // القرار يُتخذ هنا مباشرة من $request — لا من الـ session.
        //
        // السبب: كتابة session داخل RegisterUserAction (بعد Auth::login()) غير موثوقة
        // لأن Auth::login() يُجري session migration (migrate(true)) قد يُسبب race condition
        // مع بيانات الـ session المكتوبة لاحقاً في بعض session drivers.
        //
        // الحل: نقرأ plan_intent مباشرة من $request بعد execute() — وهو متوفر دائماً
        // لأن RegisterRequest تُدرجه في rules() كحقل nullable مُتحقَّق منه.
        $planIntent  = $request->input('plan_intent');
        $cycleIntent = $request->input('cycle_intent', 'monthly');

        if (in_array($planIntent, ['pro', 'business'], true)) {
            // كتابة الـ session هنا — بعد انتهاء execute() وعمليات Auth::login() كاملة.
            // هذا يضمن أن بيانات الـ session تُكتب في النهاية دون أي تداخل.
            session([
                'paid_intent' => [
                    'plan'  => $planIntent,
                    'cycle' => in_array($cycleIntent, ['monthly', 'annual'], true)
                                ? $cycleIntent
                                : 'monthly',
                ],
            ]);

            // المستخدم حصل على 30 دقيقة pre-payment grace (من RegisterUserAction)
            // لتجاوز verified middleware — يتجه الآن لصفحة الدفع.
            return redirect()->route('billing.upgrade');
        }

        return redirect()->route('dashboard');
    }
}
