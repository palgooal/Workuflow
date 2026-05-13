<?php

namespace App\Http\Controllers;

use App\Http\Requests\Budgets\StoreBudgetRequest;
use App\Http\Requests\Budgets\UpdateBudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Project;
use App\Modules\Budgets\Actions\CreateBudgetAction;
use App\Modules\Budgets\Actions\DeleteBudgetAction;
use App\Modules\Budgets\Actions\UpdateBudgetAction;
use App\Modules\Budgets\DTOs\BudgetData;
use App\Modules\Budgets\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetService    $service,
        private readonly CreateBudgetAction $createAction,
        private readonly UpdateBudgetAction $updateAction,
        private readonly DeleteBudgetAction $deleteAction,
    ) {}

    public function index(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year',  now()->year);

        $budgets = $this->service->getBudgetsWithProgress($month, $year);
        $summary = $this->service->getSummary($month, $year);

        // بيانات للـ Filters
        $years  = range(now()->year - 2, now()->year + 2);
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس',     4 => 'أبريل',
            5 => 'مايو',  6 => 'يونيو',  7 => 'يوليو',    8 => 'أغسطس',
            9 => 'سبتمبر',10 => 'أكتوبر',11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        return view('budgets.index', compact(
            'budgets', 'summary', 'month', 'year', 'years', 'months'
        ));
    }

    public function create(): View
    {
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $projects   = Project::active()->orderBy('name')->get();
        $years      = range(now()->year - 1, now()->year + 2);
        $months     = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس',     4 => 'أبريل',
            5 => 'مايو',  6 => 'يونيو',  7 => 'يوليو',    8 => 'أغسطس',
            9 => 'سبتمبر',10 => 'أكتوبر',11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        return view('budgets.create', compact(
            'categories', 'projects', 'years', 'months', 'currentMonth', 'currentYear'
        ));
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $data = BudgetData::fromRequest($request->validated());

        // تحقق من التكرار
        if ($this->service->budgetExists(
            $data->category_id, $data->project_id,
            $data->period, $data->year, $data->month
        )) {
            return back()->withErrors([
                'duplicate' => 'توجد ميزانية مماثلة لهذه الفترة والفئة/المشروع بالفعل.'
            ])->withInput();
        }

        $this->createAction->execute($data);

        return redirect()
            ->route('budget.index')
            ->with('success', 'تم إنشاء الميزانية بنجاح.');
    }

    public function edit(Budget $budget): View
    {
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $projects   = Project::active()->orderBy('name')->get();
        $years      = range(now()->year - 1, now()->year + 2);
        $months     = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس',     4 => 'أبريل',
            5 => 'مايو',  6 => 'يونيو',  7 => 'يوليو',    8 => 'أغسطس',
            9 => 'سبتمبر',10 => 'أكتوبر',11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        return view('budgets.edit', compact('budget', 'categories', 'projects', 'years', 'months'));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);
        $data = BudgetData::fromRequest($request->validated());

        if ($this->service->budgetExists(
            $data->category_id, $data->project_id,
            $data->period, $data->year, $data->month, $budget->id
        )) {
            return back()->withErrors([
                'duplicate' => 'توجد ميزانية مماثلة لهذه الفترة والفئة/المشروع بالفعل.'
            ])->withInput();
        }

        $this->updateAction->execute($budget, $data);

        return redirect()
            ->route('budget.index')
            ->with('success', 'تم تحديث الميزانية.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);
        $this->deleteAction->execute($budget);

        return redirect()
            ->route('budget.index')
            ->with('success', 'تم حذف الميزانية.');
    }
}
