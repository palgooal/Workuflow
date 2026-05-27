<?php $__env->startSection('title', 'الديون والالتزامات'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5"
     x-data="{
         tab: '<?php echo e($tab); ?>',
         payModal: false,
         payDebtId: null,
         payDebtName: '',
         payDebtRemaining: 0,
         payDebtCurrency: 'SAR',
         payAmount: '',

         openPayModal(id, name, remaining, currency) {
             this.payDebtId        = id;
             this.payDebtName      = name;
             this.payDebtRemaining = remaining;
             this.payDebtCurrency  = currency;
             this.payAmount        = '';
             this.payModal         = true;
         },
         setFullAmount() {
             this.payAmount = this.payDebtRemaining;
         }
     }">

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">الديون والالتزامات</h1>
            <p class="mt-0.5 text-sm text-gray-500">تتبع ما عليك وما لك من ديون</p>
        </div>
        <a href="<?php echo e(route('debts.create')); ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            دين جديد
        </a>
    </div>

    
    <div class="grid grid-cols-2 gap-4">

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-lg">💸</div>
                    <div>
                        <p class="text-xs text-gray-500">دين عليّ</p>
                        <p class="text-base font-bold text-red-600">
                            <?php echo e(number_format($summary['borrowed_total'], 2)); ?>

                        </p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['borrowed_overdue'] > 0): ?>
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                        <?php echo e($summary['borrowed_overdue']); ?> متأخر
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <p class="text-xs text-gray-400">المتبقي من الديون عليك</p>
        </div>

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center text-lg">🤝</div>
                    <div>
                        <p class="text-xs text-gray-500">دين لي</p>
                        <p class="text-base font-bold text-green-600">
                            <?php echo e(number_format($summary['lent_total'], 2)); ?>

                        </p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['lent_overdue'] > 0): ?>
                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-medium">
                        <?php echo e($summary['lent_overdue']); ?> متأخر
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <p class="text-xs text-gray-400">المتبقي مما أقرضته للآخرين</p>
        </div>

    </div>

    
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">

        <div class="flex border-b border-gray-100">
            <button @click="tab = 'borrowed'"
                    :class="tab === 'borrowed'
                        ? 'border-b-2 border-indigo-500 text-indigo-600 bg-indigo-50/50'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="flex-1 flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-medium transition">
                <span>💸</span>
                <span>ديون عليّ</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($borrowed->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count() > 0): ?>
                    <span class="ml-1 px-1.5 py-0.5 text-xs bg-red-100 text-red-600 rounded-full">
                        <?php echo e($borrowed->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count()); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
            <button @click="tab = 'lent'"
                    :class="tab === 'lent'
                        ? 'border-b-2 border-indigo-500 text-indigo-600 bg-indigo-50/50'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="flex-1 flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-medium transition">
                <span>🤝</span>
                <span>ديون لي</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lent->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count() > 0): ?>
                    <span class="ml-1 px-1.5 py-0.5 text-xs bg-green-100 text-green-600 rounded-full">
                        <?php echo e($lent->where('status', '!=', \App\Support\Enums\DebtStatus::Paid)->count()); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
        </div>

        
        <div x-show="tab === 'borrowed'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($borrowed->isEmpty()): ?>
                <div class="py-14">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'لا توجد ديون عليك','description' => 'أضف ديناً اقترضته من شخص لتتبعه هنا','action' => route('debts.create') . '?type=borrowed','actionLabel' => 'إضافة دين عليّ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'لا توجد ديون عليك','description' => 'أضف ديناً اقترضته من شخص لتتبعه هنا','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('debts.create') . '?type=borrowed'),'actionLabel' => 'إضافة دين عليّ']); ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $borrowed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $debt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('debts._debt_row', ['debt' => $debt], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'lent'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lent->isEmpty()): ?>
                <div class="py-14">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'لا توجد ديون لك','description' => 'أضف ديناً أقرضته لشخص لتتبع متى يُسدَّد','action' => route('debts.create') . '?type=lent','actionLabel' => 'إضافة دين لي']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'لا توجد ديون لك','description' => 'أضف ديناً أقرضته لشخص لتتبع متى يُسدَّد','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('debts.create') . '?type=lent'),'actionLabel' => 'إضافة دين لي']); ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $debt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('debts._debt_row', ['debt' => $debt], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>

    
    <div x-show="payModal"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="payModal = false"></div>

        
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-gray-900">تسجيل دفعة</h3>
                <button @click="payModal = false"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-1">الطرف الآخر:</p>
            <p class="text-base font-semibold text-gray-900 mb-4" x-text="payDebtName"></p>

            <div class="bg-gray-50 rounded-xl p-3 mb-5 flex items-center justify-between">
                <span class="text-sm text-gray-500">المتبقي:</span>
                <span class="text-base font-bold text-red-600">
                    <span x-text="Number(payDebtRemaining).toLocaleString('en', {minimumFractionDigits: 2})"></span>
                    <span x-text="payDebtCurrency" class="text-xs mr-1"></span>
                </span>
            </div>

            <form :action="'/debts/' + payDebtId + '/record-payment'" method="POST">
                <?php echo csrf_field(); ?>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">مبلغ الدفعة</label>
                    <div class="relative">
                        <input type="number" name="amount"
                               x-model="payAmount"
                               min="0.01" step="0.01" required
                               placeholder="0.00"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                    <button type="button"
                            @click="setFullAmount()"
                            class="mt-1.5 text-xs text-indigo-600 hover:text-indigo-800 underline">
                        دفع المبلغ كاملاً
                    </button>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm font-medium rounded-xl transition">
                        تسجيل الدفعة
                    </button>
                    <button type="button" @click="payModal = false"
                            class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200
                                   text-gray-700 text-sm font-medium rounded-xl transition">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/debts/index.blade.php ENDPATH**/ ?>