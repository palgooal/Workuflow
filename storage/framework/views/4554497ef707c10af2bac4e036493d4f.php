<?php $__env->startSection('title', 'التقارير والتحليلات'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">التقارير والتحليلات</h1>
            <p class="mt-0.5 text-sm text-gray-500">تحليل مالي شامل للفترة المحددة</p>

            
            <div class="flex items-center gap-2 mt-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->currentPlan()->canExport()): ?>
                    
                    <a href="<?php echo e(route('reports.export.pdf', ['from' => $from, 'to' => $to])); ?>"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100
                              text-red-700 text-xs font-medium rounded-lg border border-red-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        تصدير PDF
                    </a>

                    
                    <a href="<?php echo e(route('reports.export.excel', ['from' => $from, 'to' => $to])); ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 hover:bg-green-100
                              text-green-700 text-xs font-medium rounded-lg border border-green-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        تصدير Excel
                    </a>
                <?php else: ?>
                    
                    <div x-data="{ show: false }" class="relative">
                        <button @click="show = !show"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 text-gray-400
                                       text-xs font-medium rounded-lg border border-gray-200 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            تصدير PDF / Excel
                        </button>
                        <div x-show="show" @click.outside="show = false"
                             class="absolute top-full mt-2 right-0 bg-white border border-gray-200 rounded-xl
                                    shadow-lg p-4 w-64 z-10 text-right">
                            <p class="text-sm font-semibold text-gray-800 mb-1">ميزة مدفوعة 🔒</p>
                            <p class="text-xs text-gray-500 mb-3">
                                تصدير التقارير متاح لمشتركي <strong>Pro</strong> و<strong>Business</strong> فقط.
                            </p>
                            <a href="<?php echo e(route('billing.index')); ?>"
                               class="block text-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700
                                      text-white text-xs font-medium rounded-lg transition">
                                ترقية الخطة الآن
                            </a>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <form method="GET" action="<?php echo e(route('reports.index')); ?>"
              class="flex items-center gap-2 flex-wrap">
            <input type="date" name="from" value="<?php echo e($from); ?>"
                   class="px-3 py-2 text-sm rounded-xl border border-gray-200
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <span class="text-gray-400 text-sm">—</span>
            <input type="date" name="to" value="<?php echo e($to); ?>"
                   class="px-3 py-2 text-sm rounded-xl border border-gray-200
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="hidden" name="cat_type" value="<?php echo e($catType); ?>">
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white
                           text-sm font-medium rounded-xl transition">
                تطبيق
            </button>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_reverse($years); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loop->index < 3): ?>
                    <a href="<?php echo e(route('reports.index', ['from' => $yr.'-01-01', 'to' => $yr.'-12-31'])); ?>"
                       class="px-3 py-2 text-sm rounded-xl border transition
                              <?php echo e(substr($from,0,4) == $yr && $to >= $yr.'-12-31' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'); ?>">
                        <?php echo e($yr); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </form>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center text-lg">📈</div>
                <p class="text-xs text-gray-500">إجمالي الدخل</p>
            </div>
            <p class="text-xl font-bold text-green-600">
                +<?php echo e(number_format($summary['income'], 2)); ?>

            </p>
            <p class="text-xs text-gray-400 mt-1">
                متوسط شهري: <?php echo e(number_format($summary['avg_income'], 2)); ?>

            </p>
        </div>

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-lg">📉</div>
                <p class="text-xs text-gray-500">إجمالي المصروفات</p>
            </div>
            <p class="text-xl font-bold text-red-600">
                -<?php echo e(number_format($summary['expenses'], 2)); ?>

            </p>
            <p class="text-xs text-gray-400 mt-1">
                متوسط شهري: <?php echo e(number_format($summary['avg_expenses'], 2)); ?>

            </p>
        </div>

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 <?php echo e($summary['net'] >= 0 ? 'bg-indigo-100' : 'bg-red-100'); ?> rounded-xl flex items-center justify-center text-lg">
                    <?php echo e($summary['net'] >= 0 ? '💰' : '⚠️'); ?>

                </div>
                <p class="text-xs text-gray-500">صافي الربح</p>
            </div>
            <p class="text-xl font-bold <?php echo e($summary['net'] >= 0 ? 'text-indigo-600' : 'text-red-600'); ?>">
                <?php echo e($summary['net'] >= 0 ? '+' : ''); ?><?php echo e(number_format($summary['net'], 2)); ?>

            </p>
            <p class="text-xs text-gray-400 mt-1">
                هامش الربح: <?php echo e($summary['profit_margin']); ?>%
            </p>
        </div>

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center text-lg">🔢</div>
                <p class="text-xs text-gray-500">عدد المعاملات</p>
            </div>
            <p class="text-xl font-bold text-purple-600">
                <?php echo e(number_format($summary['count'])); ?>

            </p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bestWorst['best']): ?>
                <p class="text-xs text-gray-400 mt-1 truncate">
                    أفضل: <?php echo e($bestWorst['best']['label']); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>

    
    <div class="bg-white border border-gray-100 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-semibold text-gray-900">الاتجاه الشهري</h2>
                <p class="text-xs text-gray-400 mt-0.5">دخل ومصروفات شهر بشهر</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span> دخل
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span> مصروفات
                </span>
            </div>
        </div>
        <div class="relative h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-900">توزيع حسب الفئة</h2>
                <div class="flex gap-1">
                    <a href="<?php echo e(route('reports.index', array_merge(request()->query(), ['cat_type' => 'expense', 'from' => $from, 'to' => $to]))); ?>"
                       class="px-2.5 py-1 text-xs rounded-lg transition
                              <?php echo e($catType === 'expense' ? 'bg-red-100 text-red-700' : 'text-gray-500 hover:bg-gray-100'); ?>">
                        مصروفات
                    </a>
                    <a href="<?php echo e(route('reports.index', array_merge(request()->query(), ['cat_type' => 'income', 'from' => $from, 'to' => $to]))); ?>"
                       class="px-2.5 py-1 text-xs rounded-lg transition
                              <?php echo e($catType === 'income' ? 'bg-green-100 text-green-700' : 'text-gray-500 hover:bg-gray-100'); ?>">
                        دخل
                    </a>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->isEmpty()): ?>
                <div class="py-10 text-center text-sm text-gray-400">لا توجد بيانات للفترة المحددة</div>
            <?php else: ?>
                
                <div class="flex items-center gap-5">
                    <div class="relative w-36 h-36 shrink-0">
                        <canvas id="donutChart"></canvas>
                    </div>
                    
                    <div class="flex-1 space-y-2 min-w-0">
                        <?php $catTotal = $categories->sum('total'); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $pct = $catTotal > 0 ? round(($cat['total'] / $catTotal) * 100, 1) : 0; ?>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-700 truncate flex items-center gap-1">
                                        <span><?php echo e($cat['icon']); ?></span>
                                        <span><?php echo e($cat['name']); ?></span>
                                    </span>
                                    <span class="text-xs font-semibold text-gray-800 shrink-0 mr-2">
                                        <?php echo e($pct); ?>%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full"
                                         style="width:<?php echo e(min($pct,100)); ?>%; background-color:<?php echo e($cat['color']); ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4">ربحية المشاريع</h2>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projects->isEmpty()): ?>
                <div class="py-10 text-center text-sm text-gray-400">لا توجد مشاريع نشطة في هذه الفترة</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0"
                                 style="background-color:<?php echo e($proj['color']); ?>"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-700 truncate font-medium"><?php echo e($proj['name']); ?></span>
                                    <span class="text-sm font-bold shrink-0 mr-2
                                                 <?php echo e($proj['net'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e($proj['net'] >= 0 ? '+' : ''); ?><?php echo e(number_format($proj['net'], 0)); ?>

                                    </span>
                                </div>
                                <div class="flex gap-3 text-xs text-gray-400">
                                    <span class="text-green-500">↑ <?php echo e(number_format($proj['income'], 0)); ?></span>
                                    <span class="text-red-400">↓ <?php echo e(number_format($proj['expenses'], 0)); ?></span>
                                    <span><?php echo e($proj['tx_count']); ?> معاملة</span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proj['income'] > 0): ?>
                                        <span class="text-indigo-400">هامش <?php echo e($proj['margin']); ?>%</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->isNotEmpty()): ?>
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-base font-semibold text-gray-900">
                تفصيل الفئات — <?php echo e($catType === 'income' ? 'الدخل' : 'المصروفات'); ?>

            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-right font-medium">الفئة</th>
                        <th class="px-5 py-3 text-right font-medium">عدد المعاملات</th>
                        <th class="px-5 py-3 text-right font-medium">الإجمالي</th>
                        <th class="px-5 py-3 text-right font-medium">النسبة</th>
                        <th class="px-5 py-3 text-right font-medium">التوزيع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $catTotal = $categories->sum('total'); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $pct = $catTotal > 0 ? round(($cat['total'] / $catTotal) * 100, 1) : 0; ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="flex items-center gap-2">
                                    <span><?php echo e($cat['icon']); ?></span>
                                    <span class="font-medium text-gray-800"><?php echo e($cat['name']); ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500"><?php echo e($cat['count']); ?></td>
                            <td class="px-5 py-3 font-semibold
                                       <?php echo e($catType === 'income' ? 'text-green-600' : 'text-red-600'); ?>">
                                <?php echo e(number_format($cat['total'], 2)); ?>

                            </td>
                            <td class="px-5 py-3 text-gray-600"><?php echo e($pct); ?>%</td>
                            <td class="px-5 py-3 w-40">
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full"
                                         style="width:<?php echo e(min($pct,100)); ?>%; background-color:<?php echo e($cat['color']); ?>">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-100">
                    <tr>
                        <td class="px-5 py-3 font-semibold text-gray-700">الإجمالي</td>
                        <td class="px-5 py-3 text-gray-500"><?php echo e($categories->sum('count')); ?></td>
                        <td class="px-5 py-3 font-bold
                                   <?php echo e($catType === 'income' ? 'text-green-700' : 'text-red-700'); ?>">
                            <?php echo e(number_format($catTotal, 2)); ?>

                        </td>
                        <td class="px-5 py-3 text-gray-500">100%</td>
                        <td class="px-5 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>


<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Trend Chart (Bar) ----
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($trend['labels'], 15, 512) ?>,
                datasets: [
                    {
                        label: 'الدخل',
                        data: <?php echo json_encode($trend['income'], 15, 512) ?>,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'المصروفات',
                        data: <?php echo json_encode($trend['expenses'], 15, 512) ?>,
                        backgroundColor: 'rgba(248, 113, 113, 0.8)',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('en', {minimumFractionDigits: 2})
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            font: { size: 11 },
                            callback: v => v.toLocaleString('en')
                        }
                    }
                }
            }
        });
    }

    // ---- Donut Chart ----
    const donutCtx = document.getElementById('donutChart');
    <?php if($categories->isNotEmpty()): ?>
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($categories->take(6)->pluck('name'), 15, 512) ?>,
                datasets: [{
                    data: <?php echo json_encode($categories->take(6)->pluck('total'), 15, 512) ?>,
                    backgroundColor: <?php echo json_encode($categories->take(6)->pluck('color'), 15, 512) ?>,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString('en', {minimumFractionDigits: 2})
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/reports/index.blade.php ENDPATH**/ ?>