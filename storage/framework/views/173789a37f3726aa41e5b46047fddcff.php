<?php $__env->startSection('title', 'المعاملات'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5">

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">المعاملات</h1>
            <p class="mt-0.5 text-sm text-gray-500">سجل كامل لجميع الدخل والمصروفات</p>
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

    
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-green-50 border border-green-100 rounded-2xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الدخل</p>
            <p class="text-xl font-bold text-green-700">+<?php echo e(number_format($summary['income'], 2)); ?></p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-2xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي المصروفات</p>
            <p class="text-xl font-bold text-red-700">-<?php echo e(number_format($summary['expenses'], 2)); ?></p>
        </div>
        <div class="<?php echo e($summary['net'] >= 0 ? 'bg-indigo-50 border-indigo-100' : 'bg-red-50 border-red-100'); ?> border rounded-2xl p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">الصافي</p>
            <p class="text-xl font-bold <?php echo e($summary['net'] >= 0 ? 'text-indigo-700' : 'text-red-700'); ?>">
                <?php echo e($summary['net'] >= 0 ? '+' : ''); ?><?php echo e(number_format($summary['net'], 2)); ?>

            </p>
        </div>
    </div>

    
    <form method="GET" action="<?php echo e(route('transactions.index')); ?>"
          class="bg-white rounded-2xl border border-gray-100 p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

            
            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                       placeholder="بحث في الوصف..."
                       class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            
            <select name="type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">كل الأنواع</option>
                <option value="income"  <?php echo e(request('type') === 'income'  ? 'selected' : ''); ?>>دخل</option>
                <option value="expense" <?php echo e(request('type') === 'expense' ? 'selected' : ''); ?>>مصروف</option>
            </select>

            
            <select name="project"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">كل المشاريع</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($project->id); ?>" <?php echo e(request('project') === $project->id ? 'selected' : ''); ?>>
                        <?php echo e($project->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>

            
            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                   class="px-3 py-2 text-sm rounded-xl border border-gray-200
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">

            
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition">
                    فلترة
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['search','type','project','date_from','date_to','category'])): ?>
                    <a href="<?php echo e(route('transactions.index')); ?>"
                       class="px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition">
                        ✕
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </form>

    
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transactions->isEmpty()): ?>
            <div class="py-16">
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'لا توجد معاملات','description' => 'ابدأ بتسجيل أول معاملة لتتبع دخلك ومصاريفك','action' => route('transactions.create'),'actionLabel' => 'إضافة معاملة']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'لا توجد معاملات','description' => 'ابدأ بتسجيل أول معاملة لتتبع دخلك ومصاريفك','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('transactions.create')),'actionLabel' => 'إضافة معاملة']); ?>
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
            
            <div class="hidden sm:grid grid-cols-12 gap-4 px-5 py-3 bg-gray-50 border-b border-gray-100
                        text-xs font-medium text-gray-500 uppercase tracking-wide">
                <div class="col-span-1">النوع</div>
                <div class="col-span-4">الوصف</div>
                <div class="col-span-2">المشروع</div>
                <div class="col-span-2">الفئة</div>
                <div class="col-span-2">التاريخ</div>
                <div class="col-span-1 text-left">المبلغ</div>
            </div>

            <div class="divide-y divide-gray-50">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex sm:grid sm:grid-cols-12 sm:gap-4 items-center px-5 py-3.5
                            hover:bg-gray-50 transition group">

                    
                    <div class="sm:col-span-1 shrink-0 ml-3 sm:ml-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                    <?php echo e($tx->isIncome() ? 'bg-green-100' : 'bg-red-100'); ?>">
                            <svg class="w-4 h-4 <?php echo e($tx->isIncome() ? 'text-green-600' : 'text-red-600'); ?>"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tx->isIncome()): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </svg>
                        </div>
                    </div>

                    
                    <div class="sm:col-span-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($tx->description); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tx->reference): ?>
                            <p class="text-xs text-gray-400"><?php echo e($tx->reference); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="hidden sm:block sm:col-span-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tx->project): ?>
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                <span class="w-2 h-2 rounded-full" style="background-color: <?php echo e($tx->project->color); ?>"></span>
                                <?php echo e(Str::limit($tx->project->name, 15)); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-xs text-gray-300">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="hidden sm:block sm:col-span-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tx->category): ?>
                            <span class="text-xs text-gray-600">
                                <?php echo e($tx->category->icon); ?> <?php echo e(Str::limit($tx->category->name, 12)); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-xs text-gray-300">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="hidden sm:block sm:col-span-2">
                        <span class="text-xs text-gray-500">
                            <?php echo e($tx->transaction_date->format('d/m/Y')); ?>

                        </span>
                    </div>

                    
                    <div class="sm:col-span-1 flex items-center gap-2 shrink-0">
                        <span class="text-sm font-bold <?php echo e($tx->isIncome() ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php echo e($tx->isIncome() ? '+' : '-'); ?><?php echo e(number_format($tx->amount, 2)); ?>

                        </span>
                        <div class="hidden group-hover:flex items-center gap-1">
                            <a href="<?php echo e(route('transactions.edit', $tx)); ?>"
                               class="p-1 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="<?php echo e(route('transactions.destroy', $tx)); ?>"
                                  onsubmit="return confirm('حذف هذه المعاملة؟')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                        class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transactions->hasPages()): ?>
                <div class="px-5 py-4 border-t border-gray-100">
                    <?php echo e($transactions->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/transactions/index.blade.php ENDPATH**/ ?>