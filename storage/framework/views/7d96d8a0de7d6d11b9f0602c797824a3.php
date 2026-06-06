<?php $__env->startSection('title', 'لوحة التحكم'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showOnboarding): ?>
        <?php if (isset($component)) { $__componentOriginald3a3af92ea48f21e2bcdf2e355201bc1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3a3af92ea48f21e2bcdf2e355201bc1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding-widget','data' => ['steps' => $onboardingSteps,'progress' => $onboardingProgress,'completed' => $onboardingCompleted,'total' => $onboardingTotal]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onboardingSteps),'progress' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onboardingProgress),'completed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onboardingCompleted),'total' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($onboardingTotal)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3a3af92ea48f21e2bcdf2e355201bc1)): ?>
<?php $attributes = $__attributesOriginald3a3af92ea48f21e2bcdf2e355201bc1; ?>
<?php unset($__attributesOriginald3a3af92ea48f21e2bcdf2e355201bc1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3a3af92ea48f21e2bcdf2e355201bc1)): ?>
<?php $component = $__componentOriginald3a3af92ea48f21e2bcdf2e355201bc1; ?>
<?php unset($__componentOriginald3a3af92ea48f21e2bcdf2e355201bc1); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                مرحباً، <?php echo e(explode(' ', auth()->user()->name)[0]); ?> 👋
            </h1>
            <p class="mt-0.5 text-sm text-gray-500"><?php echo e(now()->translatedFormat('l، d F Y')); ?></p>
        </div>
        <a href="<?php echo e(route('transactions.create')); ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            معاملة جديدة
        </a>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">دخل الشهر</p>
                    <p class="mt-1.5 text-2xl font-bold text-gray-900">+<?php echo e(number_format($kpis['income']['value'], 2)); ?></p>
                </div>
                <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kpis['income']['change'] !== null): ?>
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                    <?php echo e($kpis['income']['change'] >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                    <?php echo e($kpis['income']['change'] >= 0 ? '↑' : '↓'); ?> <?php echo e(abs($kpis['income']['change'])); ?>%
                </span>
                <span class="text-xs text-gray-400">عن الشهر الماضي</span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">مصروفات الشهر</p>
                    <p class="mt-1.5 text-2xl font-bold text-gray-900">-<?php echo e(number_format($kpis['expenses']['value'], 2)); ?></p>
                </div>
                <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kpis['expenses']['change'] !== null): ?>
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                    <?php echo e($kpis['expenses']['change'] <= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                    <?php echo e($kpis['expenses']['change'] >= 0 ? '↑' : '↓'); ?> <?php echo e(abs($kpis['expenses']['change'])); ?>%
                </span>
                <span class="text-xs text-gray-400">عن الشهر الماضي</span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php $net = $kpis['net']['value']; $inc = $kpis['income']['value']; $pct = $inc > 0 ? min(round(($kpis['expenses']['value'] / $inc) * 100), 100) : 0; ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">صافي الشهر</p>
                    <p class="mt-1.5 text-2xl font-bold <?php echo e($net >= 0 ? 'text-indigo-700' : 'text-red-700'); ?>">
                        <?php echo e($net >= 0 ? '+' : ''); ?><?php echo e(number_format($net, 2)); ?>

                    </p>
                </div>
                <div class="w-11 h-11 <?php echo e($net >= 0 ? 'bg-indigo-50' : 'bg-red-50'); ?> rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 <?php echo e($net >= 0 ? 'text-indigo-600' : 'text-red-600'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full <?php echo e($pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500')); ?>"
                         style="width: <?php echo e($pct); ?>%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1"><?php echo e($pct); ?>% من الدخل مصاريف</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">المشاريع النشطة</p>
                    <p class="mt-1.5 text-2xl font-bold text-gray-900"><?php echo e($kpis['projects_active']['value']); ?></p>
                </div>
                <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?php echo e(route('projects.index')); ?>" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                    عرض جميع المشاريع ←
                </a>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">التدفق النقدي — آخر 6 أشهر</h2>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-3 h-1 bg-green-500 rounded inline-block"></span> دخل</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-1 bg-red-400 rounded inline-block"></span> مصروف</span>
                </div>
            </div>
            <canvas id="cashFlowChart" height="180"></canvas>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">ديون مستحقة قريباً</h2>
                <a href="<?php echo e(route('debts.index')); ?>" class="text-xs text-indigo-600 hover:text-indigo-700">الكل</a>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($debtsDue->isEmpty()): ?>
                <div class="py-10 text-center">
                    <p class="text-3xl mb-2">✅</p>
                    <p class="text-sm text-gray-500">لا توجد ديون مستحقة</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $debtsDue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $debt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="px-5 py-3.5 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                    <?php echo e($debt->type->value === 'borrowed' ? 'bg-red-100' : 'bg-green-100'); ?>">
                            <span class="text-sm"><?php echo e($debt->type->value === 'borrowed' ? '⬆' : '⬇'); ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($debt->party_name); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($debt->due_date->format('d/m/Y')); ?></p>
                        </div>
                        <span class="text-sm font-bold text-gray-700 shrink-0"><?php echo e(number_format($debt->remaining_amount, 0)); ?></span>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">آخر المعاملات</h2>
                <a href="<?php echo e(route('transactions.index')); ?>" class="text-xs text-indigo-600 hover:text-indigo-700">عرض الكل</a>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recent->isEmpty()): ?>
                <div class="py-12">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'لا توجد معاملات بعد','description' => 'ابدأ بإضافة معاملتك الأولى','action' => route('transactions.create'),'actionLabel' => 'إضافة معاملة']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'لا توجد معاملات بعد','description' => 'ابدأ بإضافة معاملتك الأولى','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('transactions.create')),'actionLabel' => 'إضافة معاملة']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                                    <?php echo e($tx->isIncome() ? 'bg-green-100' : 'bg-red-100'); ?>">
                            <svg class="w-4 h-4 <?php echo e($tx->isIncome() ? 'text-green-600' : 'text-red-600'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tx->isIncome()): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($tx->description); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($tx->category?->icon); ?> <?php echo e($tx->category?->name ?? 'بدون فئة'); ?> · <?php echo e($tx->transaction_date->format('d/m/Y')); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tx->project): ?>
                        <span class="hidden sm:flex items-center gap-1 text-xs text-gray-500 shrink-0">
                            <span class="w-2 h-2 rounded-full" style="background-color:<?php echo e($tx->project->color); ?>"></span>
                            <?php echo e(Str::limit($tx->project->name, 12)); ?>

                        </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="text-sm font-bold <?php echo e($tx->isIncome() ? 'text-green-600' : 'text-red-600'); ?> shrink-0">
                            <?php echo e($tx->isIncome() ? '+' : '-'); ?><?php echo e(number_format($tx->amount, 2)); ?>

                        </span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">المشاريع النشطة</h2>
                <a href="<?php echo e(route('projects.index')); ?>" class="text-xs text-indigo-600 hover:text-indigo-700">الكل</a>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projects->isEmpty()): ?>
                <div class="py-8 text-center px-4">
                    <p class="text-2xl mb-2">📁</p>
                    <p class="text-sm text-gray-500 mb-3">لا توجد مشاريع نشطة</p>
                    <a href="<?php echo e(route('projects.create')); ?>" class="text-xs text-indigo-600 font-medium">إنشاء مشروع ←</a>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0"
                                 style="background-color: <?php echo e($project->color); ?>1A">
                                <?php echo e($project->type->icon()); ?>

                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="<?php echo e(route('projects.show', $project)); ?>"
                                   class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                    <?php echo e($project->name); ?>

                                </a>
                                <p class="text-xs text-gray-400"><?php echo e($project->transactions_count); ?> معاملة</p>
                            </div>
                        </div>
                        <?php $pnet = $project->netProfit(); ?>
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-gray-400">الصافي</span>
                            <span class="font-bold <?php echo e($pnet >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                <?php echo e($pnet >= 0 ? '+' : ''); ?><?php echo e(number_format($pnet, 0)); ?> <?php echo e($project->currency); ?>

                            </span>
                        </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('cashFlowChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart['months'], 15, 512) ?>,
            datasets: [
                { label: 'دخل', data: <?php echo json_encode($chart['income'], 15, 512) ?>, backgroundColor: 'rgba(16,185,129,0.8)', borderRadius: 6, borderSkipped: false },
                { label: 'مصروف', data: <?php echo json_encode($chart['expenses'], 15, 512) ?>, backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 6, borderSkipped: false }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Tajawal' } } },
                y: { grid: { color: '#f3f4f6' }, ticks: { font: { family: 'Tajawal' }, callback: v => v.toLocaleString() } }
            }
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/dashboard.blade.php ENDPATH**/ ?>