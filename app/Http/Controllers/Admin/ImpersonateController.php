<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    /**
     * دخول كمستخدم — يحفظ ID الأدمن في الجلسة ثم يسجّل دخول المستخدم المستهدف
     */
    public function impersonate(Request $request, int $userId): RedirectResponse
    {
        // التأكد من أن من يطلب هذا هو super_admin
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $target = User::findOrFail($userId);

        // لا يمكن انتحال هوية super_admin آخر
        abort_if($target->hasRole('super_admin'), 403, 'لا يمكن الدخول بهوية مدير آخر.');

        // احفظ ID الأدمن للعودة لاحقاً
        Session::put('impersonator_id', $request->user()->id);

        // سجّل دخول المستخدم المستهدف
        Auth::login($target);

        return redirect()->route('dashboard')
            ->with('impersonating', "أنت تتصفح كـ {$target->name} — <a href='" . route('admin.impersonate.leave') . "' class='underline font-bold'>العودة للأدمن</a>");
    }

    /**
     * العودة إلى حساب الأدمن
     */
    public function leave(Request $request): RedirectResponse
    {
        $impersonatorId = Session::pull('impersonator_id');

        if (! $impersonatorId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($impersonatorId);

        if (! $admin || ! $admin->hasRole('super_admin')) {
            Auth::logout();
            return redirect()->route('login');
        }

        Auth::login($admin);

        return redirect('/admin/users')
            ->with('success', 'عدت إلى حسابك كمدير.');
    }
}
