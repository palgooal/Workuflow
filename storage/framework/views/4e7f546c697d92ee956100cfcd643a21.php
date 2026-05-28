<?php $__env->startSection('title', 'عروض الأسعار'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5">

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">عروض الأسعار</h1>
            <p class="text-sm text-gray-500 mt-0.5">إدارة عروض الأسعار المرسلة للعملاء</p>
        </div>
        <a href="<?php echo e(route('quotes.create')); ?>"
           class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium
                  px-4 py-2.5 rounded-xl hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            عرض سعر جديد
        </a>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo e($stats->total ?? 0); ?></div>
            <div class="text-xs text-gray-500 mt-0.5">إجمالي العروض</div>
        </div>
        <div class="bg-blue-50 rounded-xl border border-blue-100 p-4 text-center">
            <div class="text-2xl font-bold text-blue-700"><?php echo e($stats->pending ?? 0); ?></div>
            <div class="text-xs text-blue-500 mt-0.5">في الانتظار</div>
        </div>
        <div class="bg-teal-50 rounded-xl border border-teal-100 p-4 text-center">
            <div class="text-2xl font-bold text-teal-700"><?php echo e($stats->accepted ?? 0); ?></div>
            <div class="text-xs text-teal-500 mt-0.5">مقبولة</div>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-100 p-4 text-center">
            <div class="text-2xl font-bold text-red-700"><?php echo e($stats->rejected ?? 0); ?></div>
            <div class="text-xs text-red-500 mt-0.5">مرفوضة</div>
        </div>
        <div class="bg-purple-50 rounded-xl border border-purple-100 p-4 text-center">
            <div class="text-2xl font-bold text-purple-700"><?php echo e($stats->converted ?? 0); ?></div>
            <div class="text-xs text-purple-500 mt-0.5">محوّلة لفاتورة</div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotes->isEmpty()): ?>
            <div class="py-16 text-center">
                <div class="text-4xl mb-3">📋</div>
                <p class="text-gray-500 font-medium">لا توجد عروض أسعار بعد</p>
                <p class="text-sm text-gray-400 mt-1">ابدأ بإنشاء أول عرض سعر لعملائك</p>
                <a href="<?php echo e(route('quotes.create')); ?>"
                   class="inline-flex items-center gap-2 mt-4 bg-indigo-600 text-white text-sm
                          font-medium px-4 py-2 rounded-xl hover:bg-indigo-700 transition">
                    + إنشاء عرض سعر
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">رقم العرض</th>
                            <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">العميل</th>
                            <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">المشروع</th>
                            <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">الحالة</th>
                            <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">تاريخ الإصدار</th>
                            <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">صالح حتى</th>
                            <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">الإجمالي</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isExpired = $quote->isExpired();
                            $statusLabel = $isExpired ? 'منتهي الصلاحية' : $quote->status->label();
                            $statusClass  = $isExpired ? 'bg-orange-100 text-orange-700' : $quote->status->badgeClass();
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <a href="<?php echo e(route('quotes.show', $quote->ulid)); ?>"
                                   class="font-semibold text-indigo-600 hover:text-indigo-800">
                                    <?php echo e($quote->number); ?>

                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quote->title): ?>
                                    <div class="text-xs text-gray-400 truncate max-w-32"><?php echo e($quote->title); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-700"><?php echo e($quote->client->name); ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($quote->project?->name ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full <?php echo e($statusClass); ?>">
                                    <?php echo e($quote->status->icon()); ?> <?php echo e($statusLabel); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($quote->issue_date->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-xs
                                <?php echo e($isExpired ? 'text-red-500 font-medium' : 'text-gray-500'); ?>">
                                <?php echo e($quote->valid_until?->format('d/m/Y') ?? '—'); ?>

                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                <?php echo e(number_format($quote->total, 2)); ?>

                                <span class="text-xs font-normal text-gray-400"><?php echo e($quote->currency); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="<?php echo e(route('quotes.show', $quote->ulid)); ?>"
                                   class="text-xs text-indigo-600 hover:underline">عرض</a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotes->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100">
                    <?php echo e($quotes->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/quotes/index.blade.php ENDPATH**/ ?>