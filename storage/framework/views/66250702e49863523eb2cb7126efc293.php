<?php $__env->startSection('title', 'الإعدادات'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6"
     x-data="{ tab: window.location.hash.replace('#','') || 'profile' }">

    
    <div>
        <h1 class="text-xl font-bold text-gray-900">الإعدادات</h1>
        <p class="mt-0.5 text-sm text-gray-500">إدارة حسابك وتفضيلاتك</p>
    </div>

    
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
            ['id' => 'profile',     'label' => '👤 الملف الشخصي'],
            ['id' => 'security',    'label' => '🔒 الأمان'],
            ['id' => 'preferences', 'label' => '⚙️ التفضيلات'],
            ['id' => 'plan',        'label' => '💼 الخطة'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button @click="tab = '<?php echo e($t['id']); ?>'; window.location.hash = '<?php echo e($t['id']); ?>'"
                :class="tab === '<?php echo e($t['id']); ?>'
                    ? 'bg-white text-gray-900 shadow-sm'
                    : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 px-3 text-xs sm:text-sm font-medium rounded-lg transition">
            <?php echo e($t['label']); ?>

        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="tab === 'profile'" id="profile">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <span class="text-xl">👤</span> بيانات الملف الشخصي
            </h2>

            <form method="POST" action="<?php echo e(route('settings.profile')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

                
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-700 font-bold text-2xl">
                            <?php echo e(mb_substr($user->name, 0, 1)); ?>

                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($user->name); ?></p>
                        <p class="text-xs text-gray-400 mt-0.5"><?php echo e($user->email); ?></p>
                        <p class="text-xs text-indigo-500 mt-0.5">خطة <?php echo e($user->currentPlan()->label()); ?></p>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم الكامل</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                  <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">البريد الإلكتروني</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                  <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                                   text-sm font-medium rounded-xl transition">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div x-show="tab === 'security'" id="security" style="display:none">
        <div class="space-y-5">

            
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="text-xl">🔑</span> تغيير كلمة المرور
                </h2>

                <form method="POST" action="<?php echo e(route('settings.password')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                      <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">كلمة المرور الجديدة</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm
                                      <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <p class="mt-1 text-xs text-gray-400">8 أحرف على الأقل، تتضمن أرقاماً وحروفاً</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                                       text-sm font-medium rounded-xl transition">
                            تغيير كلمة المرور
                        </button>
                    </div>
                </form>
            </div>

            
            <div class="bg-white rounded-2xl border border-red-100 p-6"
                 x-data="{ confirmDelete: false }">
                <h2 class="text-base font-semibold text-red-700 mb-2 flex items-center gap-2">
                    <span class="text-xl">⚠️</span> حذف الحساب
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    سيتم حذف جميع بياناتك بشكل نهائي ولا يمكن التراجع عن هذا الإجراء.
                </p>

                <button @click="confirmDelete = true" x-show="!confirmDelete"
                        class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-700
                               text-sm font-medium rounded-xl border border-red-200 transition">
                    حذف الحساب نهائياً
                </button>

                <div x-show="confirmDelete" x-transition class="space-y-3">
                    <p class="text-sm font-medium text-red-700">
                        أدخل كلمة المرور للتأكيد:
                    </p>
                    <form method="POST" action="<?php echo e(route('settings.delete-account')); ?>" class="flex gap-3">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <input type="password" name="password" required
                               placeholder="كلمة المرور"
                               class="flex-1 px-4 py-2 rounded-xl border border-red-200
                                      focus:outline-none focus:ring-2 focus:ring-red-400 text-sm">
                        <button type="submit"
                                class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white
                                       text-sm font-medium rounded-xl transition">
                            تأكيد الحذف
                        </button>
                        <button type="button" @click="confirmDelete = false"
                                class="px-4 py-2 bg-gray-100 text-gray-600 text-sm rounded-xl hover:bg-gray-200 transition">
                            إلغاء
                        </button>
                    </form>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->accountDeletion->any()): ?>
                        <p class="text-xs text-red-600"><?php echo e($errors->accountDeletion->first('password')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    
    <div x-show="tab === 'preferences'" id="preferences" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <span class="text-xl">⚙️</span> التفضيلات
            </h2>

            <form method="POST" action="<?php echo e(route('settings.preferences')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">العملة الافتراضية</label>
                    <select name="currency"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($code); ?>" <?php echo e($user->currency === $code ? 'selected' : ''); ?>>
                                <?php echo e($code); ?> — <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="mt-1 text-xs text-gray-400">تُستخدم كعملة افتراضية في المعاملات الجديدة</p>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">المنطقة الزمنية</label>
                    <select name="timezone"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $timezones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tz => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($tz); ?>" <?php echo e($user->timezone === $tz ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['timezone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                                   text-sm font-medium rounded-xl transition">
                        حفظ التفضيلات
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div x-show="tab === 'plan'" id="plan" style="display:none">
        <div class="space-y-4">

            
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="text-xl">💼</span> خطتك الحالية
                </h2>

                <div class="flex items-center gap-4 p-4 rounded-xl
                    <?php echo e($user->currentPlan()->value === 'free' ? 'bg-gray-50 border border-gray-200' : 'bg-indigo-50 border border-indigo-200'); ?>">
                    <div class="text-3xl">
                        <?php echo e($user->currentPlan()->value === 'free' ? '🆓' : ($user->currentPlan()->value === 'pro' ? '⭐' : '🏢')); ?>

                    </div>
                    <div>
                        <p class="font-bold text-gray-900">خطة <?php echo e($user->currentPlan()->label()); ?></p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->currentPlan()->value === 'free'): ?>
                                الخطة المجانية — محدودة المميزات
                            <?php elseif($user->currentPlan()->value === 'pro'): ?>
                                خطة Pro — للمستقلين والعمل الاحترافي
                            <?php else: ?>
                                خطة Business — للشركات والمؤسسات
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">حدود استخدامك الحالية</h3>
                <div class="space-y-3">
                    <?php
                        $plan          = $user->currentPlan();
                        $projectsUsed  = $user->projects()->count();
                        $projectsMax   = $plan->maxProjects();
                        $txThisMonth   = $user->transactions()->whereMonth('transaction_date', now()->month)->count();
                        $txMax         = $plan->maxTransactionsPerMonth();
                    ?>

                    
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>المشاريع</span>
                            <span><?php echo e($projectsUsed); ?> / <?php echo e($projectsMax === PHP_INT_MAX ? '∞' : $projectsMax); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projectsMax !== PHP_INT_MAX): ?>
                            <?php $pct = min(round(($projectsUsed / $projectsMax) * 100), 100); ?>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full <?php echo e($pct >= 90 ? 'bg-red-400' : 'bg-indigo-400'); ?>"
                                     style="width:<?php echo e($pct); ?>%"></div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>معاملات هذا الشهر</span>
                            <span><?php echo e($txThisMonth); ?> / <?php echo e($txMax === PHP_INT_MAX ? '∞' : $txMax); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($txMax !== PHP_INT_MAX): ?>
                            <?php $pct = min(round(($txThisMonth / $txMax) * 100), 100); ?>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full <?php echo e($pct >= 90 ? 'bg-red-400' : 'bg-indigo-400'); ?>"
                                     style="width:<?php echo e($pct); ?>%"></div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="pt-2 space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            ['label' => 'تصدير البيانات', 'enabled' => $plan->canExport()],
                            ['label' => 'التقارير المتقدمة', 'enabled' => $plan->hasAdvancedReports()],
                            ['label' => 'الوصول للـ API',   'enabled' => $plan->canAccessApi()],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="<?php echo e($feature['enabled'] ? 'text-green-500' : 'text-gray-300'); ?>">
                                <?php echo e($feature['enabled'] ? '✓' : '✗'); ?>

                            </span>
                            <span class="<?php echo e($feature['enabled'] ? 'text-gray-700' : 'text-gray-400'); ?>">
                                <?php echo e($feature['label']); ?>

                            </span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->currentPlan()->value !== 'business'): ?>
            <div class="bg-gradient-to-l from-indigo-600 to-violet-600 rounded-2xl p-6 text-white">
                <p class="font-bold text-lg mb-1">🚀 ارتقِ بتجربتك</p>
                <p class="text-sm text-indigo-100 mb-4">
                    ترقية للـ Pro تمنحك مشاريع غير محدودة، 500 معاملة شهرياً، وتقارير متقدمة.
                </p>
                <a href="<?php echo e(route('settings.index')); ?>"
                   class="inline-flex items-center gap-2 px-5 py-2 bg-white text-indigo-700
                          font-semibold text-sm rounded-xl hover:bg-indigo-50 transition">
                    عرض الخطط
                    <span>←</span>
                </a>
                <p class="text-xs text-indigo-200 mt-3">سيتوفر نظام الاشتراكات قريباً (Phase 11)</p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // فتح التبويب الصحيح إذا كان هناك fragment في الـ URL
    document.addEventListener('DOMContentLoaded', function () {
        const hash = window.location.hash.replace('#', '');
        if (hash && ['profile', 'security', 'preferences', 'plan'].includes(hash)) {
            // Alpine.js سيتولى الأمر من خلال x-data
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/settings/index.blade.php ENDPATH**/ ?>