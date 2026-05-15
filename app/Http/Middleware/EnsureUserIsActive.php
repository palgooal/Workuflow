<?php

namespace App\Http\Middleware;

use App\Support\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * يمنع المستخدمين الموقوفين من الدخول إلى التطبيق
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status === UserStatus::Suspended) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'تم تعليق حسابك. يرجى التواصل مع الدعم.']);
        }

        return $next($request);
    }
}
