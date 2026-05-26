<?php $__env->startSection('title', 'إضافة عميل جديد'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto space-y-5">

    
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('clients.index')); ?>"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">إضافة عميل جديد</h1>
            <p class="text-sm text-gray-500">أدخل بيانات العميل لإضافته إلى قاعدة عملائك</p>
        </div>
    </div>

    
    <form method="POST" action="<?php echo e(route('clients.store')); ?>" class="space-y-5">
        <?php echo csrf_field(); ?>

        
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                البيانات الأساسية
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        الاسم الكامل <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                           placeholder="مثال: أحمد محمد"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none
                                  <?php echo e($errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200'); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                           placeholder="ahmed@example.com"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none
                                  <?php echo e($errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200'); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                           placeholder="+970501234567"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الشركة</label>
                    <input type="text" name="company" value="<?php echo e(old('company')); ?>"
                           placeholder="اسم الشركة أو المؤسسة"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المنصب / الوظيفة</label>
                    <input type="text" name="position" value="<?php echo e(old('position')); ?>"
                           placeholder="مثال: مدير تقني"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                التصنيف والمصدر
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        الحالة <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                   focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="prospect" <?php echo e(old('status', 'prospect') === 'prospect' ? 'selected' : ''); ?>>⭐ عميل محتمل</option>
                        <option value="active"   <?php echo e(old('status') === 'active'               ? 'selected' : ''); ?>>✅ نشط</option>
                        <option value="inactive" <?php echo e(old('status') === 'inactive'             ? 'selected' : ''); ?>>⏸ غير نشط</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">مصدر الاكتساب</label>
                    <select name="source"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                   focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">غير محدد</option>
                        <option value="direct"      <?php echo e(old('source') === 'direct'      ? 'selected' : ''); ?>>🤝 مباشر</option>
                        <option value="referral"    <?php echo e(old('source') === 'referral'    ? 'selected' : ''); ?>>📢 إحالة</option>
                        <option value="social_media"<?php echo e(old('source') === 'social_media'? 'selected' : ''); ?>>📱 وسائل التواصل</option>
                        <option value="website"     <?php echo e(old('source') === 'website'     ? 'selected' : ''); ?>>🌐 الموقع الإلكتروني</option>
                        <option value="other"       <?php echo e(old('source') === 'other'       ? 'selected' : ''); ?>>📌 أخرى</option>
                    </select>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tags->isNotEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                الوسوم
            </h2>
            <div class="flex flex-wrap gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tags->where('type', 'custom'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="tag_ids[]" value="<?php echo e($tag->id); ?>"
                           <?php echo e(in_array($tag->id, old('tag_ids', [])) ? 'checked' : ''); ?>

                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white"
                          style="background-color: <?php echo e($tag->color ?? '#6366f1'); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tag->icon): ?><?php echo e($tag->icon); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php echo e($tag->name); ?>

                    </span>
                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                معلومات تكميلية
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الموقع الإلكتروني</label>
                    <input type="url" name="website" value="<?php echo e(old('website')); ?>"
                           placeholder="https://example.com"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المدينة</label>
                    <input type="text" name="city" value="<?php echo e(old('city')); ?>"
                           placeholder="المدينة"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الدولة</label>
                    <input type="text" name="country" value="<?php echo e(old('country', 'PS')); ?>"
                           placeholder="PS"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="3" placeholder="أي ملاحظات إضافية عن العميل…"
                          class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                 focus:ring-2 focus:ring-indigo-500 outline-none resize-none"><?php echo e(old('notes')); ?></textarea>
            </div>
        </div>

        
        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('clients.index')); ?>"
               class="px-4 py-2.5 text-sm text-gray-600 bg-white border border-gray-200
                      rounded-xl hover:bg-gray-50 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm
                           font-medium rounded-xl transition">
                إضافة العميل
            </button>
        </div>

    </form>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/crm/clients/create.blade.php ENDPATH**/ ?>