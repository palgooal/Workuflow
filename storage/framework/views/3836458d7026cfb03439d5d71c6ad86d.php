<?php $__env->startSection('title', 'الفواتير'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5">

    
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">الفواتير</h1>
            <p class="text-sm text-gray-500">جميع فواتيرك في مكان واحد</p>
        </div>
        <a href="<?php echo e(route('invoices.create')); ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            فاتورة جديدة
        </a>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoices->total() > 0): ?>
    <?php
        $allInvoices = $invoices->getCollection();
        $totalPaid    = $allInvoices->where('status.value', 'paid')->sum('total');
        $totalPending = $allInvoices->whereNotIn('status.value', ['paid','cancelled'])->sum('total');
    ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">إجمالي الفواتير</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($invoices->total()); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">مسودة / مُرسَلة</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">
                <?php echo e($allInvoices->whereIn('status.value', ['draft','sent'])->count()); ?>

            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">مدفوعة</p>
            <p class="text-2xl font-bold text-teal-600 mt-1">
                <?php echo e($allInvoices->where('status.value', 'paid')->count()); ?>

            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">متأخرة</p>
            <p class="text-2xl font-bold text-red-500 mt-1">
                <?php echo e($allInvoices->filter(fn($i) => $i->isOverdue())->count()); ?>

            </p>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoices->isEmpty()): ?>
        <div class="flex flex-col items-center justify-center py-20 text-gray-400">
            <svg class="w-14 h-14 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-base font-medium text-gray-500 mb-1">لا توجد فواتير بعد</p>
            <p class="text-sm text-gray-400 mb-4">ابدأ بإنشاء أول فاتورة لعميلك</p>
            <a href="<?php echo e(route('invoices.create')); ?>"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                إنشاء فاتورة
            </a>
        </div>
        <?php else: ?>

        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">رقم الفاتورة</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">العميل</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">المشروع</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">الحالة</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">تاريخ الإصدار</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">الاستحقاق</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">الإجمالي</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50/50 transition group">
                        <td class="py-3.5 px-4">
                            <a href="<?php echo e(route('invoices.show', $invoice->ulid)); ?>"
                               class="font-semibold text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
                                <?php echo e($invoice->number); ?>

                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->title): ?>
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[160px]"><?php echo e($invoice->title); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="<?php echo e(route('clients.show', $invoice->client->public_id)); ?>"
                               class="text-sm text-gray-800 hover:text-indigo-600 transition">
                                <?php echo e($invoice->client->name); ?>

                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client->company): ?>
                            <p class="text-xs text-gray-400"><?php echo e($invoice->client->company); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 hidden md:table-cell">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->project): ?>
                            <span class="text-sm text-gray-600"><?php echo e($invoice->project->name); ?></span>
                            <?php else: ?>
                            <span class="text-xs text-gray-300">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium w-fit <?php echo e($invoice->status->badgeClass()); ?>">
                                    <?php echo e($invoice->status->icon()); ?> <?php echo e($invoice->status->label()); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->isOverdue()): ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 w-fit">
                                    ⚠️ متأخرة
                                </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 hidden lg:table-cell text-sm text-gray-600">
                            <?php echo e($invoice->issue_date->format('Y/m/d')); ?>

                        </td>
                        <td class="py-3.5 px-4 hidden lg:table-cell">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->due_date): ?>
                            <span class="text-sm <?php echo e($invoice->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-600'); ?>">
                                <?php echo e($invoice->due_date->format('Y/m/d')); ?>

                            </span>
                            <?php else: ?>
                            <span class="text-xs text-gray-300">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-left">
                            <span class="text-sm font-semibold text-gray-900">
                                <?php echo e(number_format($invoice->total, 2)); ?>

                            </span>
                            <span class="text-xs text-gray-400 ml-0.5"><?php echo e($invoice->currency); ?></span>
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="<?php echo e(route('invoices.show', $invoice->ulid)); ?>"
                               class="inline-flex items-center justify-center w-7 h-7 text-gray-300
                                      hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoices->hasPages()): ?>
        <div class="px-4 py-4 border-t border-gray-100">
            <?php echo e($invoices->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/invoices/index.blade.php ENDPATH**/ ?>