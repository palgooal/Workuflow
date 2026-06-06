<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['steps', 'progress', 'completed', 'total']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['steps', 'progress', 'completed', 'total']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{ visible: true }"
     x-show="visible"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="bg-white border border-indigo-100 rounded-2xl overflow-hidden shadow-sm">

    
    <div class="bg-gradient-to-l from-indigo-600 to-violet-600 px-5 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center text-lg">🚀</div>
                <div>
                    <h3 class="text-sm font-bold text-white">ابدأ رحلتك مع دراهم</h3>
                    <p class="text-xs text-indigo-200 mt-0.5">
                        <?php echo e($completed); ?> من <?php echo e($total); ?> خطوات مكتملة
                    </p>
                </div>
            </div>

            
            <form method="POST" action="<?php echo e(route('onboarding.dismiss')); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        @click="visible = false"
                        class="w-7 h-7 bg-white/10 hover:bg-white/20 rounded-lg flex items-center
                               justify-center text-white/70 hover:text-white transition"
                        title="إخفاء">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </div>

        
        <div class="mt-3">
            <div class="flex justify-between text-xs text-indigo-200 mb-1.5">
                <span>التقدم</span>
                <span><?php echo e($progress); ?>%</span>
            </div>
            <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                <div class="h-full bg-white rounded-full transition-all duration-700"
                     style="width: <?php echo e($progress); ?>%"></div>
            </div>
        </div>
    </div>

    
    <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-start gap-3 p-3 rounded-xl
                            <?php echo e($step['completed']
                                ? 'bg-green-50 border border-green-100'
                                : 'bg-gray-50 border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition'); ?>">

                    
                    <div class="shrink-0 mt-0.5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step['completed']): ?>
                            <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        <?php else: ?>
                            <div class="w-8 h-8 bg-white border-2 border-gray-200 rounded-xl flex items-center
                                        justify-center text-base">
                                <?php echo e($step['icon']); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold
                                  <?php echo e($step['completed'] ? 'text-green-700 line-through' : 'text-gray-800'); ?>">
                            <?php echo e($step['title']); ?>

                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$step['completed']): ?>
                            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                <?php echo e($step['description']); ?>

                            </p>
                            <a href="<?php echo e(route($step['url_name'])); ?>"
                               class="inline-flex items-center gap-1 mt-2 text-xs font-medium
                                      text-indigo-600 hover:text-indigo-800 transition">
                                <?php echo e($step['url_label']); ?>

                                <svg class="w-3 h-3 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <p class="text-xs text-green-600 mt-0.5 font-medium">✓ مكتمل</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$step['completed']): ?>
                        <span class="shrink-0 text-xs text-gray-300 font-mono mt-0.5">
                            <?php echo e($index + 1); ?>/<?php echo e(count($steps)); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progress === 100): ?>
            <div class="mt-3 p-3 bg-green-50 border border-green-100 rounded-xl text-center">
                <p class="text-sm font-semibold text-green-700">🎉 أحسنت! أكملت جميع الخطوات</p>
                <p class="text-xs text-green-600 mt-0.5">حسابك جاهز بالكامل. استمتع بـ دراهم!</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/onboarding-widget.blade.php ENDPATH**/ ?>