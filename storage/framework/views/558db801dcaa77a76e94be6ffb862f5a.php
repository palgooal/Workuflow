<div class="bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow overflow-hidden
            <?php echo e($project->is_active ? '' : 'opacity-60'); ?>"
     x-data="{ menuOpen: false }">

    
    <div class="h-1.5 w-full" style="background-color: <?php echo e($project->color); ?>"></div>

    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                
                <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center"
                     style="background-color: <?php echo e($project->color); ?>1A; border: 2px solid <?php echo e($project->color); ?>40">
                    <span class="text-lg"><?php echo e($project->type->icon()); ?></span>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate"><?php echo e($project->name); ?></h3>
                    <p class="text-xs text-gray-400 mt-0.5"><?php echo e($project->currency); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$project->is_active): ?>
                            · <span class="text-orange-400">متوقف</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>
            </div>

            
            <div class="relative shrink-0">
                <button @click="menuOpen = !menuOpen" @click.outside="menuOpen = false"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </button>
                <div x-show="menuOpen" x-transition
                     class="absolute left-0 mt-1 w-40 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-10">
                    <a href="<?php echo e(route('projects.show', $project)); ?>"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        عرض التفاصيل
                    </a>
                    <a href="<?php echo e(route('projects.edit', $project)); ?>"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        تعديل
                    </a>
                    <hr class="my-1 border-gray-100">
                    <form method="POST" action="<?php echo e(route('projects.destroy', $project)); ?>"
                          onsubmit="return confirm('هل أنت متأكد من حذف مشروع <?php echo e(addslashes($project->name)); ?>؟')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->description): ?>
            <p class="mt-3 text-sm text-gray-500 line-clamp-2"><?php echo e($project->description); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php
            $income   = $project->totalIncome();
            $expenses = $project->totalExpenses();
            $net      = $project->netProfit();
        ?>

        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="bg-green-50 rounded-xl p-2">
                <p class="text-xs text-gray-400 mb-0.5">دخل</p>
                <p class="text-xs font-bold text-green-700"><?php echo e(number_format($income, 0)); ?></p>
            </div>
            <div class="bg-red-50 rounded-xl p-2">
                <p class="text-xs text-gray-400 mb-0.5">مصروف</p>
                <p class="text-xs font-bold text-red-700"><?php echo e(number_format($expenses, 0)); ?></p>
            </div>
            <div class="<?php echo e($net >= 0 ? 'bg-indigo-50' : 'bg-red-50'); ?> rounded-xl p-2">
                <p class="text-xs text-gray-400 mb-0.5">صافي</p>
                <p class="text-xs font-bold <?php echo e($net >= 0 ? 'text-indigo-700' : 'text-red-700'); ?>">
                    <?php echo e($net >= 0 ? '+' : ''); ?><?php echo e(number_format($net, 0)); ?>

                </p>
            </div>
        </div>

        
        <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
            <span><?php echo e($project->transactions_count ?? 0); ?> معاملة</span>
            <a href="<?php echo e(route('projects.show', $project)); ?>"
               class="text-indigo-600 hover:text-indigo-700 font-medium">
                عرض التفاصيل ←
            </a>
        </div>
    </div>
</div>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/projects/_card.blade.php ENDPATH**/ ?>