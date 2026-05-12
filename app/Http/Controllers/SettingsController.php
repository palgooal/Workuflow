<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdatePreferencesRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $currencies = [
            'SAR' => 'ريال سعودي',
            'AED' => 'درهم إماراتي',
            'KWD' => 'دينار كويتي',
            'QAR' => 'ريال قطري',
            'BHD' => 'دينار بحريني',
            'OMR' => 'ريال عُماني',
            'JOD' => 'دينار أردني',
            'EGP' => 'جنيه مصري',
            'MAD' => 'درهم مغربي',
            'TND' => 'دينار تونسي',
            'LYD' => 'دينار ليبي',
            'USD' => 'دولار أمريكي',
            'EUR' => 'يورو',
            'GBP' => 'جنيه إسترليني',
        ];

        $timezones = [
            'Asia/Riyadh'    => 'الرياض (توقيت السعودية)',
            'Asia/Dubai'     => 'دبي (توقيت الإمارات)',
            'Asia/Kuwait'    => 'الكويت',
            'Asia/Qatar'     => 'قطر',
            'Asia/Bahrain'   => 'البحرين',
            'Asia/Muscat'    => 'مسقط (عُمان)',
            'Asia/Amman'     => 'عمّان (الأردن)',
            'Africa/Cairo'   => 'القاهرة (مصر)',
            'Africa/Casablanca' => 'الدار البيضاء (المغرب)',
            'Africa/Tunis'   => 'تونس',
            'Africa/Tripoli' => 'طرابلس (ليبيا)',
            'Asia/Baghdad'   => 'بغداد (العراق)',
            'Asia/Damascus'  => 'دمشق (سوريا)',
            'Asia/Beirut'    => 'بيروت (لبنان)',
            'Asia/Gaza'      => 'غزة (فلسطين)',
            'Asia/Hebron'    => 'الخليل (فلسطين)',
            'Asia/Aden'      => 'عدن (اليمن)',
            'Africa/Khartoum'=> 'الخرطوم (السودان)',
            'UTC'            => 'UTC (التوقيت العالمي)',
        ];

        return view('settings.index', compact('user', 'currencies', 'timezones'));
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($data['email'] !== $user->email) {
            $data['email_verified_at'] = null; // TODO: إعادة التحقق في Phase 13
        }

        $user->update($data);

        return redirect()
            ->route('settings.index')
            ->with('success', 'تم تحديث بيانات الملف الشخصي.')
            ->withFragment('profile');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'password.required'                 => 'كلمة المرور الجديدة مطلوبة.',
            'password.confirmed'                => 'تأكيد كلمة المرور لا يتطابق.',
            'password.min'                      => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('settings.index')
            ->with('success', 'تم تغيير كلمة المرور بنجاح.')
            ->withFragment('security');
    }

    public function updatePreferences(UpdatePreferencesRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()
            ->route('settings.index')
            ->with('success', 'تم حفظ التفضيلات.')
            ->withFragment('preferences');
    }

    public function deleteAccount(Request $request): RedirectResponse
    {
        $request->validateWithBag('accountDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'تم حذف الحساب بنجاح.');
    }
}
