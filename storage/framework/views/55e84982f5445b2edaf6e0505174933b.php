<?php $__env->startSection('title', 'مركز المساعدة'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">مركز المساعدة</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div
    x-data="{ tab: 'start' }"
    class="flex gap-6"
>

    
    <aside class="w-56 shrink-0 hidden md:block">
        <div class="bg-white rounded-2xl border border-gray-100 p-3 sticky top-6 space-y-1">

            <p class="px-3 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">الأقسام</p>

            <?php
            $tabs = [
                ['id' => 'start',        'label' => 'البداية السريعة',   'emoji' => '🚀'],
                ['id' => 'projects',     'label' => 'المشاريع',           'emoji' => '📁'],
                ['id' => 'transactions', 'label' => 'المعاملات',          'emoji' => '💸'],
                ['id' => 'clients',      'label' => 'العملاء',            'emoji' => '👥'],
                ['id' => 'team',         'label' => 'الفريق',             'emoji' => '🧑‍💼'],
                ['id' => 'debts',        'label' => 'الديون',             'emoji' => '💳'],
                ['id' => 'budget',       'label' => 'الميزانية',          'emoji' => '📊'],
                ['id' => 'recurring',    'label' => 'الالتزامات الثابتة', 'emoji' => '🔁'],
                ['id' => 'reports',      'label' => 'التقارير',           'emoji' => '📈'],
                ['id' => 'tips',         'label' => 'نصائح وحيل',         'emoji' => '💡'],
            ];
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button
                @click="tab = '<?php echo e($t['id']); ?>'"
                :class="tab === '<?php echo e($t['id']); ?>' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                class="w-full text-right flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm transition"
            >
                <span class="text-base"><?php echo e($t['emoji']); ?></span>
                <?php echo e($t['label']); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
    </aside>

    
    <div class="flex-1 min-w-0 space-y-4">

        
        <div class="md:hidden bg-white rounded-2xl border border-gray-100 p-3">
            <select x-model="tab" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t['id']); ?>"><?php echo e($t['emoji']); ?> <?php echo e($t['label']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>

        
        <div x-show="tab === 'start'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '🚀','title' => 'البداية السريعة — أول 5 دقائق']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '🚀','title' => 'البداية السريعة — أول 5 دقائق']); ?>

                <?php if (isset($component)) { $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-step','data' => ['number' => '1','title' => 'أنشئ مشروعك الأول']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-step'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['number' => '1','title' => 'أنشئ مشروعك الأول']); ?>
                    اذهب إلى <strong>المشاريع</strong> من القائمة الجانبية ← اضغط <strong>"مشروع جديد"</strong>.
                    أدخل اسم المشروع، نوعه (تجاري أو شخصي)، العملة، والعميل إن وُجد.
                    إذا كان لديك عقد مع العميل، أدخل <strong>قيمة العقد</strong> لتتبع نسبة ما استلمته.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $attributes = $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $component = $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-step','data' => ['number' => '2','title' => 'أضف الخدمات التي تقدمها']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-step'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['number' => '2','title' => 'أضف الخدمات التي تقدمها']); ?>
                    داخل نموذج المشروع، في قسم الخدمات اختر ما تقدمه (تصميم، سيو، موشن...).
                    حدد مبلغ كل خدمة ونوعه (دخل أو مصروف).
                    يمكنك تعيين عضو من فريقك على كل خدمة وتحديد تكلفته.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $attributes = $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $component = $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-step','data' => ['number' => '3','title' => 'سجّل أول معاملة']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-step'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['number' => '3','title' => 'سجّل أول معاملة']); ?>
                    افتح المشروع ← اضغط <strong>"إضافة معاملة"</strong>.
                    اختر النوع (دخل أو مصروف)، أدخل المبلغ والوصف والتاريخ.
                    لحظة التسجيل، ستُحدَّث جميع الإحصاءات تلقائياً.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $attributes = $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $component = $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-step','data' => ['number' => '4','title' => 'راقب لوحة التحكم']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-step'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['number' => '4','title' => 'راقب لوحة التحكم']); ?>
                    لوحة التحكم تُلخّص كل شيء: دخلك الشهري، مصروفاتك، أرباحك، ومشاريعك النشطة —
                    كل شيء في مكان واحد.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $attributes = $__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__attributesOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560)): ?>
<?php $component = $__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560; ?>
<?php unset($__componentOriginalf3c0c5b1bfe90e0cbe139a98f659a560); ?>
<?php endif; ?>

                <div class="mt-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                    <p class="text-sm font-semibold text-indigo-800 mb-1">💡 الدورة الطبيعية للاستخدام:</p>
                    <p class="text-sm text-indigo-700 leading-relaxed">
                        مشروع جديد ← إضافة خدمات وعميل ← تسجيل دفعات (دخل) ← تسجيل مصروفات ← مراقبة الأرباح من صفحة المشروع.
                    </p>
                </div>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'projects'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '📁','title' => 'المشاريع']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '📁','title' => 'المشاريع']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'ما هو المشروع؟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'ما هو المشروع؟']); ?>
                    المشروع هو <strong>وحدة التتبع المالي الأساسية</strong>. كل دخل أو مصروف تسجله يُربط بمشروع،
                    مما يعطيك صورة كاملة عن ربحية كل مشروع على حدة.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'أنواع المشاريع']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'أنواع المشاريع']); ?>
                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="text-xl">💼</span>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">تجاري</p>
                                <p class="text-gray-500 text-xs">مشاريع العملاء والأعمال — يظهر في تقارير الأعمال</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-xl">🏠</span>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">شخصي</p>
                                <p class="text-gray-500 text-xs">المصاريف الشخصية والمنزلية — مفصول عن مالية العمل</p>
                            </div>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'الحقول المالية']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'الحقول المالية']); ?>
                    <div class="space-y-3">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">📄 قيمة العقد</p>
                            <p class="text-gray-500 text-xs mt-0.5">المبلغ المتفق عليه مع العميل. يظهر شريط تقدم يوضح كم استلمت منه حتى الآن (أزرق → أخضر عند الاكتمال).</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">💰 ميزانية التكاليف</p>
                            <p class="text-gray-500 text-xs mt-0.5">الحد الأقصى للمصروفات الذي خططت له. يتحول الشريط أحمر تلقائياً عند التجاوز.</p>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'المقاييس المالية في صفحة المشروع']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'المقاييس المالية في صفحة المشروع']); ?>
                    <div class="grid grid-cols-2 gap-3">
                        <?php $metrics = [
                            ['إجمالي الدخل', 'مجموع كل معاملات الدخل', 'text-green-700', 'bg-green-50'],
                            ['إجمالي المصروفات', 'مجموع كل معاملات المصروف', 'text-red-700', 'bg-red-50'],
                            ['صافي الربح', 'الدخل − المصروفات. أخضر = ربح، أحمر = خسارة', 'text-blue-700', 'bg-blue-50'],
                            ['هامش الربح %', '30%+ ممتاز · 10-30% جيد · أقل من 0% = خسارة', 'text-purple-700', 'bg-purple-50'],
                        ] ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$name, $desc, $text, $bg]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-3 <?php echo e($bg); ?> rounded-xl">
                            <p class="font-semibold text-sm <?php echo e($text); ?>"><?php echo e($name); ?></p>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed"><?php echo e($desc); ?></p>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    عند تجاوز مصروفات المشروع لميزانيته، ستظهر تنبيه أحمر يوضح مقدار التجاوز مباشرةً في صفحة المشروع.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'transactions'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '💸','title' => 'المعاملات']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '💸','title' => 'المعاملات']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'نوعا المعاملة']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'نوعا المعاملة']); ?>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-2.5 bg-green-50 rounded-xl">
                            <div class="w-8 h-8 bg-green-200 rounded-lg flex items-center justify-center text-green-700 font-bold">↑</div>
                            <div>
                                <p class="font-semibold text-green-900 text-sm">دخل</p>
                                <p class="text-green-700 text-xs">مدفوعات من العملاء، دفعات مقدمة، إيرادات...</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-2.5 bg-red-50 rounded-xl">
                            <div class="w-8 h-8 bg-red-200 rounded-lg flex items-center justify-center text-red-700 font-bold">↓</div>
                            <div>
                                <p class="font-semibold text-red-900 text-sm">مصروف</p>
                                <p class="text-red-700 text-xs">أدوات، خدمات، رواتب، مستلزمات...</p>
                            </div>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'حقل جهة الدفع (Payee)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'حقل جهة الدفع (Payee)']); ?>
                    يظهر فقط عند اختيار <strong>مصروف</strong>. استخدمه لتسجيل من دفعت له:
                    اسم المورد، الشركة، الفريلانسر... يساعدك لاحقاً في تتبع أين تذهب أموالك.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'ربط المعاملة بمشروع وفئة']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'ربط المعاملة بمشروع وفئة']); ?>
                    <p class="text-sm text-gray-600">كل معاملة يمكن ربطها بـ:</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">•</span> <strong>مشروع</strong> — لتظهر في تقرير المشروع وتؤثر على أرباحه</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">•</span> <strong>فئة</strong> — لتصنيفها في التقارير (إيجار، أدوات، تسويق...)</li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    يمكنك تصفية المعاملات بالمشروع أو النوع أو التاريخ من صفحة المعاملات الرئيسية.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'clients'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '👥','title' => 'العملاء']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '👥','title' => 'العملاء']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'ما فائدة إضافة العملاء؟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'ما فائدة إضافة العملاء؟']); ?>
                    ربط العميل بالمشروع يمنحك:
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> تتبع المشاريع لكل عميل بشكل منفصل</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> التواصل المباشر عبر واتساب من بطاقة العميل</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> سجل مرجعي بجميع بيانات عملائك في مكان واحد</li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'بطاقة العميل تحتوي على']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'بطاقة العميل تحتوي على']); ?>
                    الاسم، الشركة، رقم الهاتف، البريد الإلكتروني، الموقع، والملاحظات.
                    من البطاقة يمكنك مباشرةً فتح محادثة واتساب مع العميل بنقرة واحدة.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    تأكد من إدخال رقم الهاتف بالصيغة الدولية (مثال: 966501234567) لكي يعمل زر الواتساب بشكل صحيح.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'team'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '🧑‍💼','title' => 'الفريق']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '🧑‍💼','title' => 'الفريق']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'نوعا أعضاء الفريق']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'نوعا أعضاء الفريق']); ?>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">موظف</span>
                            <p class="text-sm text-gray-600">مرتبط بالشركة بشكل دائم، راتب ثابت</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">فريلانسر</span>
                            <p class="text-sm text-gray-600">مستقل يُدفع له بالمشروع أو المهمة</p>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'ربط عضو الفريق بخدمة في المشروع']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'ربط عضو الفريق بخدمة في المشروع']); ?>
                    عند إنشاء أو تعديل مشروع، داخل كل خدمة يمكنك:
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> تعيين عضو الفريق المسؤول عن هذه الخدمة</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> تحديد تكلفته (team cost) على هذه الخدمة</li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'زر "تسجيل دفعة"']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'زر "تسجيل دفعة"']); ?>
                    في صفحة تفاصيل المشروع، قسم <strong>"الفريق المعين على المشروع"</strong>:
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-green-500">•</span> اضغط "تسجيل دفعة" ← تُنشأ معاملة مصروف تلقائياً باسم العضو</li>
                        <li class="flex items-start gap-2"><span class="text-green-500">•</span> تتحول حالة الدفع من "⏳ لم يُدفع" إلى "✅ تم الدفع"</li>
                        <li class="flex items-start gap-2"><span class="text-green-500">•</span> يُحسب المبلغ تلقائياً في مصروفات المشروع</li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    أدخل "المعدل الافتراضي" (default rate) لكل عضو، سيُقترح تلقائياً عند تعيينه على خدمة.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'debts'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '💳','title' => 'الديون والالتزامات']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '💳','title' => 'الديون والالتزامات']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'متى تستخدم الديون؟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'متى تستخدم الديون؟']); ?>
                    <div class="space-y-2">
                        <div class="p-2.5 bg-orange-50 rounded-xl">
                            <p class="text-sm font-semibold text-orange-800">دين عليك (لازم تدفعه)</p>
                            <p class="text-xs text-orange-700 mt-0.5">مثال: اشتريت معدات بالتقسيط، أو اقترضت من شخص</p>
                        </div>
                        <div class="p-2.5 bg-blue-50 rounded-xl">
                            <p class="text-sm font-semibold text-blue-800">دين لك (لازم تستلمه)</p>
                            <p class="text-xs text-blue-700 mt-0.5">مثال: العميل لم يدفع بعد، أقرضت شخصاً</p>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'كيف تتعامل مع الديون']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'كيف تتعامل مع الديون']); ?>
                    عند تسجيل دين، يمكنك:
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> <strong>تسجيل دفعة جزئية</strong> — تُحدَّث نسبة السداد تلقائياً</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> <strong>تمييز كمدفوع كلياً</strong> — يُغلق الدين ويُزال من القائمة النشطة</li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    ديونك تؤثر على لوحة التحكم تحت مؤشر "إجمالي الالتزامات" — راقبه لتعرف وضعك المالي الحقيقي.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'budget'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '📊','title' => 'الميزانية']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '📊','title' => 'الميزانية']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'الفرق بين الميزانية وميزانية التكاليف في المشروع']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'الفرق بين الميزانية وميزانية التكاليف في المشروع']); ?>
                    <div class="space-y-2">
                        <div class="p-2.5 bg-indigo-50 rounded-xl">
                            <p class="text-sm font-semibold text-indigo-800">📊 الميزانية (Budget Module)</p>
                            <p class="text-xs text-indigo-700 mt-0.5">ميزانية شهرية أو سنوية عامة لفئة معينة (مثال: 2000 ريال للتسويق شهرياً)</p>
                        </div>
                        <div class="p-2.5 bg-orange-50 rounded-xl">
                            <p class="text-sm font-semibold text-orange-800">💰 ميزانية التكاليف في المشروع</p>
                            <p class="text-xs text-orange-700 mt-0.5">سقف مصروفات خاص بمشروع بعينه (مثال: مصروفات مشروع X لا تتجاوز 1500)</p>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'كيف تعمل الميزانية؟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'كيف تعمل الميزانية؟']); ?>
                    تُنشئ ميزانية لفئة معينة (مثل: إيجار، أدوات، تسويق) بمبلغ محدد وفترة زمنية.
                    التطبيق يتبع مصروفاتك في تلك الفئة ويُنبهك عند الاقتراب من الحد.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    استخدم الفئات باتساق عند تسجيل المعاملات حتى تعمل الميزانيات بدقة.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'recurring'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '🔁','title' => 'الالتزامات الثابتة (Recurring)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '🔁','title' => 'الالتزامات الثابتة (Recurring)']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'ما هي الالتزامات الثابتة؟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'ما هي الالتزامات الثابتة؟']); ?>
                    مصروفات أو دخل يتكرر بانتظام:
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-red-400">•</span> إيجار المكتب، اشتراك Adobe، اشتراك الاستضافة...</li>
                        <li class="flex items-start gap-2"><span class="text-green-400">•</span> راتب ثابت، دخل شهري متكرر من عميل...</li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'دورات التكرار المتاحة']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'دورات التكرار المتاحة']); ?>
                    <div class="grid grid-cols-3 gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['يومي','أسبوعي','شهري','كل شهرين','ربع سنوي','نصف سنوي','سنوي']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="text-xs text-center px-2 py-1.5 bg-gray-100 rounded-lg text-gray-700"><?php echo e($period); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'زر "نفّذ الآن"']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'زر "نفّذ الآن"']); ?>
                    إذا جاء موعد دفع أحد الالتزامات، اضغط <strong>"نفّذ الآن"</strong> ← ستُنشأ معاملة فعلية تلقائياً وتُضاف لقائمة معاملاتك.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    يمكنك إيقاف أي التزام مؤقتاً بدون حذفه، ثم تفعيله لاحقاً عند الحاجة.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'reports'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '📈','title' => 'التقارير']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '📈','title' => 'التقارير']); ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'ما الذي تعرضه التقارير؟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'ما الذي تعرضه التقارير؟']); ?>
                    <ul class="space-y-1.5 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> الدخل والمصروف الشهري مع مقارنة الأشهر</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> أكثر الفئات إنفاقاً</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> ربحية المشاريع مقارنةً ببعضها</li>
                        <li class="flex items-start gap-2"><span class="text-indigo-500">•</span> توزيع الدخل حسب المشاريع أو الفئات</li>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-card','data' => ['title' => 'تصدير التقارير']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'تصدير التقارير']); ?>
                    <div class="flex gap-3">
                        <div class="flex-1 p-3 bg-red-50 rounded-xl text-center">
                            <p class="text-2xl">📄</p>
                            <p class="text-sm font-semibold text-red-800 mt-1">PDF</p>
                            <p class="text-xs text-red-600">مناسب للطباعة أو الإرسال للعميل</p>
                        </div>
                        <div class="flex-1 p-3 bg-green-50 rounded-xl text-center">
                            <p class="text-2xl">📊</p>
                            <p class="text-sm font-semibold text-green-800 mt-1">Excel</p>
                            <p class="text-xs text-green-600">مناسب للتحليل أو المحاسب</p>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $attributes = $__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__attributesOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd)): ?>
<?php $component = $__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd; ?>
<?php unset($__componentOriginal8e47d4c8fab4e479b1ea64e2d9e677dd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginale841acf5e17e7f077a17024b63306b46 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale841acf5e17e7f077a17024b63306b46 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-tip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-tip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    يمكنك فلترة التقارير حسب الفترة الزمنية: هذا الشهر، الشهر الماضي، 3 أشهر، أو نطاق مخصص.
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $attributes = $__attributesOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__attributesOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale841acf5e17e7f077a17024b63306b46)): ?>
<?php $component = $__componentOriginale841acf5e17e7f077a17024b63306b46; ?>
<?php unset($__componentOriginale841acf5e17e7f077a17024b63306b46); ?>
<?php endif; ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

        
        <div x-show="tab === 'tips'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-section','data' => ['emoji' => '💡','title' => 'نصائح وحيل']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('help-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['emoji' => '💡','title' => 'نصائح وحيل']); ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <?php $tips = [
                        ['🎨', 'استخدم الألوان', 'أعطِ لكل مشروع لون مميز حتى تتعرف عليه بسرعة في القوائم.'],
                        ['🏷️', 'صنّف معاملاتك', 'حدد فئة لكل معاملة لكي تكون التقارير دقيقة ومفيدة.'],
                        ['📅', 'سجّل يومياً', 'تسجيل المعاملات فور حدوثها أفضل من تأجيلها — لن تنساها.'],
                        ['💬', 'استخدم الوصف', 'اكتب وصفاً واضحاً لكل معاملة حتى تعرف سببها لاحقاً.'],
                        ['🔁', 'أتمتة المتكرر', 'أي مصروف يتكرر شهرياً أضفه كالتزام ثابت لتوفير الوقت.'],
                        ['📊', 'راقع الميزانية أسبوعياً', 'نظرة سريعة أسبوعياً على مؤشرات الميزانية تمنع المفاجآت.'],
                        ['👥', 'أضف كل عملائك', 'حتى لو لم يكن لديهم مشاريع الآن — يسهّل التواصل مستقبلاً.'],
                        ['📱', 'رقم الواتساب الدولي', 'أدخل أرقام الهاتف بالصيغة الدولية (966xxxxxxxx) لكي يعمل الواتساب.'],
                    ] ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$emoji, $title, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 hover:border-indigo-200 transition">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl shrink-0"><?php echo e($emoji); ?></span>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm"><?php echo e($title); ?></p>
                                <p class="text-gray-500 text-xs mt-1 leading-relaxed"><?php echo e($desc); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                </div>

                
                <div class="mt-4 p-5 bg-gradient-to-l from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100">
                    <p class="font-semibold text-gray-900 mb-1">لديك سؤال لم تجد إجابته هنا؟</p>
                    <p class="text-sm text-gray-600">تواصل معنا عبر البريد الإلكتروني أو واتساب وسنساعدك فوراً.</p>
                </div>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $attributes = $__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__attributesOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4)): ?>
<?php $component = $__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4; ?>
<?php unset($__componentOriginalf55ef852921f5e3b76b94f0ce39bfff4); ?>
<?php endif; ?>
        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/help/index.blade.php ENDPATH**/ ?>