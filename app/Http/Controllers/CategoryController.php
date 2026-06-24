<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Modules\Categories\Actions\CreateCategoryAction;
use App\Modules\Categories\Actions\DeleteCategoryAction;
use App\Modules\Categories\Actions\UpdateCategoryAction;
use App\Modules\Categories\DTOs\CategoryData;
use App\Support\Enums\TransactionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CreateCategoryAction $createAction,
        private readonly UpdateCategoryAction $updateAction,
        private readonly DeleteCategoryAction $deleteAction,
    ) {}

    public function index(): View
    {
        $income   = Category::income()->withCount('transactions')->orderBy('is_default', 'desc')->orderBy('name')->get();
        $expenses = Category::expense()->withCount('transactions')->orderBy('is_default', 'desc')->orderBy('name')->get();
        $colors   = $this->defaultColors();
        $icons    = $this->defaultIcons();

        return view('categories.index', compact('income', 'expenses', 'colors', 'icons'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->createAction->execute(
            CategoryData::fromRequest($request->validated())
        );

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم إنشاء الفئة بنجاح.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->updateAction->execute(
            $category,
            CategoryData::fromRequest($request->validated())
        );

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم تحديث الفئة بنجاح.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        try {
            $this->deleteAction->execute($category);
            return redirect()
                ->route('categories.index')
                ->with('success', 'تم حذف الفئة بنجاح.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('categories.index')
                ->with('error', $e->errors()['category'][0]);
        }
    }

    private function defaultColors(): array
    {
        return [
            '#6366F1', '#8B5CF6', '#EC4899', '#EF4444',
            '#F97316', '#F59E0B', '#10B981', '#14B8A6',
            '#3B82F6', '#64748B', '#84CC16', '#06B6D4',
        ];
    }

    private function defaultIcons(): array
    {
        return [
            'المال والأعمال'    => ['💰','💵','💴','💳','🏦','💼','📊','💹','🪙','💎'],
            'المنزل والسكن'     => ['🏠','🏡','🛋️','🔑','💡','🔧','🪟','🛁'],
            'التسوق'            => ['🛒','🛍️','👕','👟','🎁','🧴','💄'],
            'التنقل والسفر'     => ['🚗','✈️','⛽','🚕','🚌','🛵','🚢','🗺️'],
            'الطعام والمشروبات' => ['🍔','☕','🍕','🥗','🥤','🍜','🍣','🍰'],
            'الصحة واللياقة'    => ['💊','🏋️','🏥','🧘','🩺','🦷'],
            'التعليم والثقافة'  => ['🎓','📚','📝','🔬','📖','✏️','🔭'],
            'الترفيه'           => ['🎵','🎮','🎬','📺','🎨','🎭','🎤'],
            'التقنية'           => ['📱','💻','🖥️','⌨️','📡','📷'],
            'أخرى'              => ['🌍','⚡','📈','📉','📦','🔒','🌟','🎯'],
        ];
    }
}
