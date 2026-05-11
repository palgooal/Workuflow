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

        return view('auth.register', compact('currencies', 'timezones'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        app(RegisterUserAction::class)->execute($request);

        return redirect()->route('dashboard');
    }
}
