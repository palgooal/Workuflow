<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        // الخدمات تُدار من إعدادات المشاريع — redirect
        return redirect()->route('projects.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'color'   => ['nullable', 'string'],
        ]);

        $data['user_id']   = auth()->id();
        $data['is_global'] = false;
        $data['is_active'] = true;

        Service::create($data);

        return back()->with('success', 'تم إضافة الخدمة "' . $data['name_ar'] . '".');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if (! $service->is_global && $service->user_id === auth()->id()) {
            $service->delete();
        }

        return back()->with('success', 'تم حذف الخدمة.');
    }
}
