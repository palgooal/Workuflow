<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class OnboardingController extends Controller
{
    public function dismiss(): RedirectResponse
    {
        auth()->user()->update([
            'onboarding_dismissed_at' => now(),
        ]);

        return back();
    }
}
