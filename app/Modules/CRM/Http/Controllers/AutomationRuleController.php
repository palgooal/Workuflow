<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Automation\BaseAutomationAction;
use App\Modules\CRM\Models\AutomationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AutomationRuleController — إدارة قواعد الأتمتة
 *
 * Sprint 6 — S6.3
 *
 * Web: Blade Views | AJAX: JSON
 */
class AutomationRuleController extends Controller
{
    // ==================== index ====================

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Client::class);

        $userId = $request->user()->id;

        $rules = AutomationRule::where('user_id', $userId)
            ->orderBy('priority')
            ->orderBy('created_at')
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['data' => $rules]);
        }

        $triggers = AutomationRule::triggers();
        $actionTypes = collect(BaseAutomationAction::all())
            ->map(fn($class) => ['type' => $class::type(), 'label' => $class::label()])
            ->values();

        return view('crm.automation-rules.index', compact('rules', 'triggers', 'actionTypes'));
    }

    // ==================== store ====================

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Client::class);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'trigger'    => ['required', 'string', 'in:' . implode(',', array_keys(AutomationRule::triggers()))],
            'conditions' => ['nullable', 'array'],
            'actions'    => ['required', 'array', 'min:1'],
            'actions.*.type'   => ['required', 'string', 'in:' . implode(',', array_keys(BaseAutomationAction::all()))],
            'actions.*.params' => ['nullable', 'array'],
            'is_active'  => ['boolean'],
            'priority'   => ['integer', 'min:1', 'max:100'],
        ]);

        $validated['user_id']   = $request->user()->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['priority']  = $validated['priority'] ?? 10;
        $validated['conditions'] = $validated['conditions'] ?? null;

        $rule = AutomationRule::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['data' => $rule, 'message' => 'تم إنشاء القاعدة بنجاح.'], 201);
        }

        return redirect()
            ->route('clients.automation-rules.index')
            ->with('success', 'تم إنشاء قاعدة الأتمتة بنجاح.');
    }

    // ==================== update ====================

    public function update(Request $request, AutomationRule $automationRule): RedirectResponse|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Client::class);
        $this->ensureOwnership($automationRule, $request->user()->id);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'trigger'    => ['required', 'string', 'in:' . implode(',', array_keys(AutomationRule::triggers()))],
            'conditions' => ['nullable', 'array'],
            'actions'    => ['required', 'array', 'min:1'],
            'actions.*.type'   => ['required', 'string', 'in:' . implode(',', array_keys(BaseAutomationAction::all()))],
            'actions.*.params' => ['nullable', 'array'],
            'is_active'  => ['boolean'],
            'priority'   => ['integer', 'min:1', 'max:100'],
        ]);

        $validated['is_active']  = $request->boolean('is_active', $automationRule->is_active);
        $validated['conditions'] = $validated['conditions'] ?? null;

        $automationRule->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['data' => $automationRule->fresh(), 'message' => 'تم تحديث القاعدة بنجاح.']);
        }

        return redirect()
            ->route('clients.automation-rules.index')
            ->with('success', 'تم تحديث قاعدة الأتمتة بنجاح.');
    }

    // ==================== toggle ====================

    public function toggle(Request $request, AutomationRule $automationRule): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Client::class);
        $this->ensureOwnership($automationRule, $request->user()->id);

        $automationRule->update(['is_active' => !$automationRule->is_active]);

        return response()->json([
            'data'    => ['is_active' => $automationRule->is_active],
            'message' => $automationRule->is_active ? 'تم تفعيل القاعدة.' : 'تم إيقاف القاعدة.',
        ]);
    }

    // ==================== destroy ====================

    public function destroy(Request $request, AutomationRule $automationRule): RedirectResponse|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Client::class);
        $this->ensureOwnership($automationRule, $request->user()->id);

        $automationRule->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'تم حذف القاعدة.']);
        }

        return redirect()
            ->route('clients.automation-rules.index')
            ->with('success', 'تم حذف قاعدة الأتمتة.');
    }

    // ==================== Helpers ====================

    private function ensureOwnership(AutomationRule $rule, int $userId): void
    {
        if ($rule->user_id !== $userId) {
            abort(403);
        }
    }
}
