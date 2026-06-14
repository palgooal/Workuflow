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
            ['id' => 'invoice',     'label' => '🧾 قالب الفاتورة'],
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

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        الهامش المستهدف للخدمات
                        <span class="text-gray-400 font-normal text-xs">(%)</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        يُستخدم لاقتراح الأسعار وتنبيه الهامش المنخفض عند إنشاء المشاريع.
                    </p>
                    <div class="flex items-center gap-4">
                        <input type="range" name="target_margin_pct"
                               min="1" max="99" step="1"
                               value="<?php echo e(old('target_margin_pct', $user->target_margin_pct ?? 40)); ?>"
                               class="flex-1 accent-indigo-600"
                               x-data
                               x-model.number="$el.value"
                               @input="$el.nextElementSibling.textContent = $el.value + '%'"
                               oninput="this.nextElementSibling.textContent = this.value + '%'">
                        <span class="w-12 text-center text-sm font-bold text-indigo-700 bg-indigo-50 rounded-lg py-1">
                            <?php echo e(old('target_margin_pct', $user->target_margin_pct ?? 40)); ?>%
                        </span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-1 px-0.5">
                        <span>1%</span>
                        <span>حرص متوسط (40%)</span>
                        <span>99%</span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['target_margin_pct'];
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

            
            <?php
                $plan         = $user->currentPlan();
                $projectsUsed = $user->projects()->count();
                $projectsMax  = $plan->maxProjects();
                $txThisMonth  = $user->transactions()->whereMonth('transaction_date', now()->month)->count();
                $txMax        = $plan->maxTransactionsPerMonth();
                $invoicesCount = \App\Models\Invoice::where('user_id', $user->id)->count();
                $clientsCount  = \App\Models\Client::where('user_id', $user->id)->count();

                $projPct  = $projectsMax !== PHP_INT_MAX ? min(round(($projectsUsed / $projectsMax) * 100), 100) : 0;
                $txPct    = $txMax       !== PHP_INT_MAX ? min(round(($txThisMonth  / $txMax)       * 100), 100) : 0;
                $nearLimit = ($projPct >= 80 || $txPct >= 80);
                $atLimit   = ($projPct >= 100 || $txPct >= 100);
            ?>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    خطتك الحالية
                </h2>

                <div class="flex items-center gap-4 p-4 rounded-xl
                    <?php echo e($plan->value === 'free' ? 'bg-gray-50 border border-gray-200' : 'bg-indigo-50 border border-indigo-200'); ?>">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                        <?php echo e($plan->value === 'free' ? 'bg-gray-200' : 'bg-indigo-600'); ?>">
                        <svg class="w-6 h-6 <?php echo e($plan->value === 'free' ? 'text-gray-500' : 'text-white'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->value === 'free'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900"><?php echo e($plan->label()); ?></p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->value === 'free'): ?>
                                ابدأ مجاناً — يمكنك الترقية في أي وقت
                            <?php elseif($plan->value === 'pro'): ?>
                                خطة Pro — للمستقلين المحترفين
                            <?php else: ?>
                                Business — للشركات والفرق
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($atLimit): ?>
            <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="text-sm">
                    <span class="font-semibold text-red-700">وصلت للحد الأقصى</span>
                    <span class="text-red-600"> — لن تتمكن من إضافة المزيد حتى الترقية.</span>
                </div>
            </div>
            <?php elseif($nearLimit): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="text-sm">
                    <span class="font-semibold text-amber-700">اقتربت من الحد</span>
                    <span class="text-amber-600"> — فكّر في الترقية قبل أن تتوقف.</span>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">استخدامك الحالي</h3>
                <div class="space-y-4">

                    
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="text-gray-600">المشاريع</span>
                            <span class="<?php echo e($projPct >= 90 ? 'text-red-600 font-semibold' : ($projPct >= 80 ? 'text-amber-600 font-medium' : 'text-gray-500')); ?>">
                                <?php echo e($projectsUsed); ?> / <?php echo e($projectsMax === PHP_INT_MAX ? '∞' : $projectsMax); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projectsMax !== PHP_INT_MAX && $projPct < 100): ?>
                                    <span class="text-xs font-normal text-gray-400">(تبقّى <?php echo e($projectsMax - $projectsUsed); ?>)</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projectsMax !== PHP_INT_MAX): ?>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                <?php echo e($projPct >= 100 ? 'bg-red-500' : ($projPct >= 80 ? 'bg-amber-400' : 'bg-indigo-400')); ?>"
                                 style="width:<?php echo e($projPct); ?>%"></div>
                        </div>
                        <?php else: ?>
                        <div class="w-full bg-indigo-100 rounded-full h-2">
                            <div class="h-2 rounded-full bg-indigo-400 w-full opacity-30"></div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="text-gray-600">معاملات هذا الشهر</span>
                            <span class="<?php echo e($txPct >= 90 ? 'text-red-600 font-semibold' : ($txPct >= 80 ? 'text-amber-600 font-medium' : 'text-gray-500')); ?>">
                                <?php echo e($txThisMonth); ?> / <?php echo e($txMax === PHP_INT_MAX ? '∞' : $txMax); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($txMax !== PHP_INT_MAX && $txPct < 100): ?>
                                    <span class="text-xs font-normal text-gray-400">(تبقّى <?php echo e($txMax - $txThisMonth); ?>)</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($txMax !== PHP_INT_MAX): ?>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                <?php echo e($txPct >= 100 ? 'bg-red-500' : ($txPct >= 80 ? 'bg-amber-400' : 'bg-indigo-400')); ?>"
                                 style="width:<?php echo e($txPct); ?>%"></div>
                        </div>
                        <?php else: ?>
                        <div class="w-full bg-indigo-100 rounded-full h-2">
                            <div class="h-2 rounded-full bg-indigo-400 w-full opacity-30"></div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="pt-2 grid grid-cols-2 gap-3 border-t border-gray-100">
                        <div class="bg-gray-50 rounded-xl px-4 py-3 text-center">
                            <p class="text-xl font-bold text-gray-800"><?php echo e($invoicesCount); ?></p>
                            <p class="text-xs text-gray-400 mt-0.5">فاتورة</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 text-center">
                            <p class="text-xl font-bold text-gray-800"><?php echo e($clientsCount); ?></p>
                            <p class="text-xs text-gray-400 mt-0.5">عميل</p>
                        </div>
                    </div>

                    
                    <div class="pt-1 space-y-2 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-400 pt-1">الميزات المتاحة</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            ['label' => 'تصدير البيانات',   'enabled' => $plan->canExport()],
                            ['label' => 'التقارير المتقدمة', 'enabled' => $plan->hasAdvancedReports()],
                            ['label' => 'الوصول للـ API',    'enabled' => $plan->canAccessApi()],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-2.5 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feature['enabled']): ?>
                                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-700"><?php echo e($feature['label']); ?></span>
                            <?php else: ?>
                                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <span class="text-gray-400 line-through decoration-gray-200"><?php echo e($feature['label']); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->value !== 'business'): ?>
            <div class="bg-gradient-to-l from-indigo-600 to-violet-600 rounded-2xl p-6 text-white">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($atLimit): ?>
                    <p class="font-bold text-lg mb-1">وصلت للحد — حان وقت الترقية</p>
                    <p class="text-sm text-indigo-100 mb-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projPct >= 100): ?> لا يمكنك إضافة مشاريع جديدة. <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($txPct  >= 100): ?> لا يمكنك تسجيل معاملات هذا الشهر. <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        الترقية إلى Pro تحل المشكلة فوراً.
                    </p>
                <?php elseif($nearLimit): ?>
                    <p class="font-bold text-lg mb-1">اقتربت من الحد 🔔</p>
                    <p class="text-sm text-indigo-100 mb-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projPct >= 80): ?> تبقّى لك <?php echo e($projectsMax - $projectsUsed); ?> مشروع فقط. <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($txPct  >= 80): ?> تبقّى لك <?php echo e($txMax - $txThisMonth); ?> معاملة هذا الشهر. <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        الترقية إلى Pro تمنحك مساحة أكبر بكثير.
                    </p>
                <?php else: ?>
                    <p class="font-bold text-lg mb-1">🚀 ارتقِ بتجربتك</p>
                    <p class="text-sm text-indigo-100 mb-4">
                        ترقية للـ Pro: 10 مشاريع، 500 معاملة شهرياً، تقارير متقدمة، وتصدير البيانات.
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="<?php echo e(route('billing.upgrade')); ?>"
                       class="inline-flex items-center gap-2 px-5 py-2 bg-white text-indigo-700
                              font-semibold text-sm rounded-xl hover:bg-indigo-50 transition">
                        ترقية الخطة
                        <span>←</span>
                    </a>
                    <a href="<?php echo e(route('billing.index')); ?>"
                       class="text-sm text-indigo-200 hover:text-white transition">
                        عرض جميع الخطط
                    </a>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
    </div>

    
    <?php
        $userId      = auth()->id();
        $invColor    = \App\Models\Setting::get("invoice_color_{$userId}", '#4f46e5');
        $invName     = \App\Models\Setting::get("invoice_company_name_{$userId}", auth()->user()->name);
        $invInfo     = \App\Models\Setting::get("invoice_company_info_{$userId}", '');
        $invFooter   = \App\Models\Setting::get("invoice_footer_{$userId}", '');
        $invLogoPath = \App\Models\Setting::get("invoice_logo_{$userId}");
    ?>
    <div x-show="tab === 'invoice'" id="invoice" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-6">
            <h2 class="text-base font-semibold text-gray-800">🧾 تخصيص قالب الفاتورة</h2>

            <form method="POST" action="<?php echo e(route('settings.invoice')); ?>" enctype="multipart/form-data" class="space-y-5">
                <?php echo csrf_field(); ?>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">شعار الشركة</label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invLogoPath): ?>
                    <div class="flex items-center gap-4 mb-3">
                        <img src="<?php echo e(Storage::url($invLogoPath)); ?>" alt="Logo" class="h-16 w-auto rounded-lg border border-gray-200 object-contain p-1">
                        <label class="flex items-center gap-2 text-sm text-red-500 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300">
                            حذف الشعار الحالي
                        </label>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <input type="file" name="invoice_logo" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:ml-0 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition">
                    <p class="text-xs text-gray-400 mt-1">PNG أو JPG — بحد أقصى 2MB</p>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">لون القالب الرئيسي</label>
                    <div class="flex items-center gap-3 flex-wrap">
                        <input type="color" name="invoice_color" value="<?php echo e($invColor); ?>"
                               x-on:input="document.getElementById('colorText').value=$el.value; document.getElementById('previewHeader').style.background=$el.value"
                               class="w-12 h-10 rounded-lg border border-gray-200 cursor-pointer p-1">
                        <input type="text" id="colorText" value="<?php echo e($invColor); ?>"
                               oninput="document.querySelector('[name=invoice_color]').value=this.value; document.getElementById('previewHeader').style.background=this.value"
                               class="w-28 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                        <div class="flex gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['#4f46e5','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#1e293b']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" onclick="setColor('<?php echo e($c); ?>')"
                                    class="w-7 h-7 rounded-full border-2 border-white shadow hover:scale-110 transition"
                                    style="background:<?php echo e($c); ?>" title="<?php echo e($c); ?>"></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">اسم الشركة / المستقل</label>
                    <input type="text" name="invoice_company_name" value="<?php echo e(old('invoice_company_name', $invName)); ?>"
                           placeholder="مثال: أحمد للتصميم"
                           class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">معلومات التواصل</label>
                    <textarea name="invoice_company_info" rows="3"
                              placeholder="العنوان، الهاتف، البريد الإلكتروني، رقم السجل التجاري..."
                              class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-none"><?php echo e(old('invoice_company_info', $invInfo)); ?></textarea>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">نص أسفل الفاتورة</label>
                    <input type="text" name="invoice_footer" value="<?php echo e(old('invoice_footer', $invFooter)); ?>"
                           placeholder="مثال: شكراً لتعاملك معنا — الدفع خلال 30 يوماً"
                           class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">معاينة</label>
                    <div id="previewHeader" class="rounded-xl p-4 flex items-center gap-3"
                         style="background: <?php echo e($invColor); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invLogoPath): ?>
                        <img src="<?php echo e(Storage::url($invLogoPath)); ?>" alt="Logo" class="h-10 w-auto object-contain">
                        <?php else: ?>
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                            <?php echo e(mb_substr($invName ?: 'A', 0, 1)); ?>

                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div>
                            <p class="text-white font-bold text-sm"><?php echo e($invName ?: 'اسم الشركة'); ?></p>
                            <p class="text-white/70 text-xs">فاتورة رقم INV-0001</p>
                        </div>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success') && str_contains(session('success') ?? '', 'فاتورة')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-2">
                    ✅ <?php echo e(session('success')); ?>

                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                        حفظ إعدادات الفاتورة
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function setColor(hex) {
    document.querySelector('[name=invoice_color]').value = hex;
    document.getElementById('colorText').value = hex;
    document.getElementById('previewHeader').style.background = hex;
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/settings/index.blade.php ENDPATH**/ ?>