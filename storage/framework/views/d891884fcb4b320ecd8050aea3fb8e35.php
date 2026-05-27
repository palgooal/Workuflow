<?php $__env->startSection('title', 'فاتورة ' . $invoice->number); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-4">

    
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('clients.show', $invoice->client->public_id)); ?>"
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900"><?php echo e($invoice->number); ?></h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($invoice->status->badgeClass()); ?>">
                        <?php echo e($invoice->status->icon()); ?> <?php echo e($invoice->status->label()); ?>

                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->isOverdue()): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        ⚠️ متأخرة
                    </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <p class="text-sm text-gray-500"><?php echo e($invoice->client->name); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->project): ?> — <?php echo e($invoice->project->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap print:hidden">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->status !== \App\Support\Enums\InvoiceStatus::Paid && $invoice->status !== \App\Support\Enums\InvoiceStatus::Cancelled): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->status === \App\Support\Enums\InvoiceStatus::Draft): ?>
            <form method="POST" action="<?php echo e(route('invoices.mark-sent', $invoice->ulid)); ?>">
                <?php echo csrf_field(); ?>
                <button class="px-3 py-2 text-sm text-blue-600 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition">
                    📤 تحديد كمُرسَلة
                </button>
            </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <form method="POST" action="<?php echo e(route('invoices.mark-paid', $invoice->ulid)); ?>">
                <?php echo csrf_field(); ?>
                <button class="px-3 py-2 text-sm text-teal-600 bg-teal-50 border border-teal-200 rounded-xl hover:bg-teal-100 transition">
                    ✅ تسجيل الدفع
                </button>
            </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <button onclick="window.print()"
                    class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                🖨️ طباعة / PDF
            </button>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->status === \App\Support\Enums\InvoiceStatus::Draft): ?>
            <a href="<?php echo e(route('invoices.edit', $invoice->ulid)); ?>"
               class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                ✏️ تعديل
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!in_array($invoice->status, [\App\Support\Enums\InvoiceStatus::Paid, \App\Support\Enums\InvoiceStatus::Cancelled])): ?>
            <form method="POST" action="<?php echo e(route('invoices.cancel', $invoice->ulid)); ?>"
                  onsubmit="return confirm('هل تريد إلغاء هذه الفاتورة؟')">
                <?php echo csrf_field(); ?>
                <button class="px-3 py-2 text-sm text-red-500 bg-white border border-red-200 rounded-xl hover:bg-red-50 transition">
                    إلغاء
                </button>
            </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm print:shadow-none print:border-0" id="invoice-paper">

        
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-3xl font-bold text-indigo-600">فاتورة</h2>
                <p class="text-gray-500 text-sm mt-1"><?php echo e($invoice->number); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->title): ?>
                <p class="text-gray-700 font-medium mt-1"><?php echo e($invoice->title); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="text-left text-sm text-gray-500 space-y-1">
                <div class="flex gap-6">
                    <div>
                        <p class="text-xs text-gray-400">تاريخ الإصدار</p>
                        <p class="font-medium text-gray-700"><?php echo e($invoice->issue_date->format('Y/m/d')); ?></p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->due_date): ?>
                    <div>
                        <p class="text-xs text-gray-400">تاريخ الاستحقاق</p>
                        <p class="font-medium <?php echo e($invoice->isOverdue() ? 'text-red-600' : 'text-gray-700'); ?>">
                            <?php echo e($invoice->due_date->format('Y/m/d')); ?>

                        </p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-2 gap-8 mb-8 pb-6 border-b border-gray-100">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">إلى</p>
                <p class="font-semibold text-gray-900"><?php echo e($invoice->client->name); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client->company): ?>
                <p class="text-sm text-gray-600"><?php echo e($invoice->client->company); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client->email): ?>
                <p class="text-sm text-gray-500"><?php echo e($invoice->client->email); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client->phone): ?>
                <p class="text-sm text-gray-500"><?php echo e($invoice->client->phone); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->project): ?>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">المشروع</p>
                <p class="font-semibold text-gray-900"><?php echo e($invoice->project->name); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-right py-2 text-gray-500 font-medium w-1/2">الوصف</th>
                    <th class="text-center py-2 text-gray-500 font-medium w-1/6">الكمية</th>
                    <th class="text-left py-2 text-gray-500 font-medium w-1/6">سعر الوحدة</th>
                    <th class="text-left py-2 text-gray-500 font-medium w-1/6">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-b border-gray-50">
                    <td class="py-3 text-gray-800"><?php echo e($item->description); ?></td>
                    <td class="py-3 text-center text-gray-600"><?php echo e(number_format($item->quantity, 2)); ?></td>
                    <td class="py-3 text-gray-600"><?php echo e(number_format($item->unit_price, 2)); ?> <?php echo e($invoice->currency); ?></td>
                    <td class="py-3 font-medium text-gray-800"><?php echo e(number_format($item->total, 2)); ?> <?php echo e($invoice->currency); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        
        <div class="flex justify-end mb-6">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>المجموع الفرعي</span>
                    <span><?php echo e(number_format($invoice->subtotal, 2)); ?> <?php echo e($invoice->currency); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->tax_rate > 0): ?>
                <div class="flex justify-between text-gray-600">
                    <span>ضريبة (<?php echo e($invoice->tax_rate); ?>%)</span>
                    <span><?php echo e(number_format($invoice->tax_amount, 2)); ?> <?php echo e($invoice->currency); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->discount > 0): ?>
                <div class="flex justify-between text-gray-600">
                    <span>خصم</span>
                    <span class="text-red-600">-<?php echo e(number_format($invoice->discount, 2)); ?> <?php echo e($invoice->currency); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-200">
                    <span>الإجمالي</span>
                    <span class="text-indigo-700"><?php echo e(number_format($invoice->total, 2)); ?> <?php echo e($invoice->currency); ?></span>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes || $invoice->terms): ?>
        <div class="border-t border-gray-100 pt-6 space-y-3 text-sm text-gray-600">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
            <div>
                <p class="font-semibold text-gray-700 mb-1">ملاحظات:</p>
                <p class="whitespace-pre-line"><?php echo e($invoice->notes); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->terms): ?>
            <div>
                <p class="font-semibold text-gray-700 mb-1">الشروط والأحكام:</p>
                <p class="whitespace-pre-line text-gray-500"><?php echo e($invoice->terms); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->status === \App\Support\Enums\InvoiceStatus::Paid): ?>
        <div class="absolute top-8 left-8 opacity-10 print:opacity-20">
            <div class="border-4 border-teal-500 text-teal-500 font-bold text-4xl px-6 py-3 rounded-xl rotate-[-15deg]">
                مدفوعة
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>

<style>
@media print {
    nav, header, .print\:hidden { display: none !important; }
    body { background: white; }
    #invoice-paper { margin: 0; }
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/invoices/show.blade.php ENDPATH**/ ?>