
<div class="bg-white rounded-2xl border border-gray-100 p-6"
     x-data="{
         selectedColor: '<?php echo e(old('color', $project->color ?? '#6366F1')); ?>',
         selectedType: '<?php echo e(old('type', $project->type->value ?? 'business')); ?>',
         allServiceOptions: <?php echo e($services->map(fn($s) => ['id' => $s->id, 'name_ar' => $s->name_ar ?? $s->name])->toJson()); ?>,
         teamMembers: <?php echo e($teamMembers->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toJson()); ?>,
         services: <?php echo e(json_encode(
             old('services', isset($project)
                 ? $project->services->map(fn($s) => [
                     'service_id'     => (string) $s->id,
                     'amount'         => $s->pivot->amount,
                     'type'           => $s->pivot->type,
                     'notes'          => $s->pivot->notes ?? '',
                     'team_member_id' => (string) ($s->pivot->team_member_id ?? ''),
                     'team_cost'      => $s->pivot->team_cost ?? '',
                 ])->toArray()
                 : []
             )
         )); ?>,
         addService() {
             this.services.push({ service_id: '', amount: '', type: 'income', notes: '', team_member_id: '', team_cost: '' });
         },
         removeService(index) {
             this.services.splice(index, 1);
         }
     }">

    <div class="space-y-5">

        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                اسم المشروع <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   name="name"
                   value="<?php echo e(old('name', $project->name ?? '')); ?>"
                   placeholder="مثال: متجر إلكتروني، تطوير تطبيق..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                          <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 bg-red-50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-xs text-red-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                نوع المشروع <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer"
                       @click="selectedType = 'business'">
                    <input type="radio" name="type" value="business"
                           <?php echo e(old('type', $project->type->value ?? 'business') === 'business' ? 'checked' : ''); ?>

                           class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                         :class="selectedType === 'business'
                             ? 'border-indigo-500 bg-indigo-50'
                             : 'border-gray-200 hover:border-gray-300'">
                        <span class="text-2xl">💼</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">تجاري</p>
                            <p class="text-xs text-gray-400">للأعمال والمشاريع التجارية</p>
                        </div>
                    </div>
                </label>
                <label class="relative cursor-pointer"
                       @click="selectedType = 'personal'">
                    <input type="radio" name="type" value="personal"
                           <?php echo e(old('type', $project->type->value ?? '') === 'personal' ? 'checked' : ''); ?>

                           class="sr-only">
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition"
                         :class="selectedType === 'personal'
                             ? 'border-indigo-500 bg-indigo-50'
                             : 'border-gray-200 hover:border-gray-300'">
                        <span class="text-2xl">🏠</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">شخصي</p>
                            <p class="text-xs text-gray-400">للمصاريف الشخصية والعائلية</p>
                        </div>
                    </div>
                </label>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-xs text-red-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="grid grid-cols-2 gap-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    العملة <span class="text-red-500">*</span>
                </label>
                <select name="currency"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               <?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 bg-red-50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($currency); ?>"
                                <?php echo e(old('currency', $project->currency ?? auth()->user()->currency) === $currency ? 'selected' : ''); ?>>
                            <?php echo e($currency); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1.5 text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    لون المشروع <span class="text-red-500">*</span>
                </label>
                <input type="hidden" name="color" :value="selectedColor">
                <div class="flex items-center gap-2 flex-wrap">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button"
                            @click="selectedColor = '<?php echo e($color); ?>'"
                            class="w-7 h-7 rounded-lg transition-all duration-150 border-2"
                            :class="selectedColor === '<?php echo e($color); ?>'
                                ? 'scale-110 border-gray-800'
                                : 'border-transparent hover:scale-105'"
                            style="background-color: <?php echo e($color); ?>">
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div class="relative">
                        <input type="color"
                               :value="selectedColor"
                               @input="selectedColor = $event.target.value"
                               class="w-7 h-7 rounded-lg cursor-pointer border border-gray-200"
                               title="اختر لوناً مخصصاً">
                    </div>
                </div>
                
                <div class="mt-2 flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full" :style="`background-color: ${selectedColor}`"></div>
                    <span class="text-xs text-gray-400" x-text="selectedColor"></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1.5 text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                وصف المشروع <span class="text-gray-400 font-normal">(اختياري)</span>
            </label>
            <textarea name="description"
                      rows="3"
                      placeholder="وصف مختصر للمشروع وأهدافه..."
                      class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                             <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 bg-red-50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $project->description ?? '')); ?></textarea>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-xs text-red-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    قيمة العقد
                    <span class="text-gray-400 font-normal">(المبلغ المتفق عليه)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <input type="number" name="contract_value" step="0.01" min="0"
                           value="<?php echo e(old('contract_value', $project->contract_value ?? '')); ?>"
                           placeholder="0.00"
                           class="w-full pr-9 pl-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  <?php $__errorArgs = ['contract_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contract_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    ميزانية التكاليف
                    <span class="text-gray-400 font-normal">(سقف المصروفات)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <input type="number" name="expense_budget" step="0.01" min="0"
                           value="<?php echo e(old('expense_budget', $project->expense_budget ?? '')); ?>"
                           placeholder="0.00"
                           class="w-full pr-9 pl-3.5 py-2.5 rounded-xl border border-gray-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  <?php $__errorArgs = ['expense_budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['expense_budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                العميل <span class="text-gray-400 font-normal">(اختياري)</span>
            </label>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clients->isEmpty()): ?>
                <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    لا يوجد عملاء بعد.
                    <a href="<?php echo e(route('clients.create')); ?>" class="font-medium underline">أضف عميلاً الآن</a>
                </div>
            <?php else: ?>
                <select name="client_id"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">— بدون عميل —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($client->id); ?>"
                                <?php echo e(old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : ''); ?>>
                            <?php echo e($client->name); ?><?php echo e($client->company ? ' — ' . $client->company : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1.5 text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-data="{
                quickAddOpen: false,
                quickName: '',
                quickLoading: false,
                quickError: '',
                async submitQuick() {
                    if (! this.quickName.trim()) return;
                    this.quickLoading = true;
                    this.quickError   = '';
                    try {
                        const res = await fetch('<?php echo e(route('services.quick-store')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ name_ar: this.quickName }),
                        });
                        if (! res.ok) throw new Error('فشل الحفظ');
                        const svc = await res.json();
                        allServiceOptions.push({ id: svc.id, name_ar: svc.name_ar });
                        services.push({ service_id: String(svc.id), amount: '', type: 'income', notes: '', team_member_id: '', team_cost: '' });
                        this.quickName   = '';
                        this.quickAddOpen = false;
                    } catch (e) {
                        this.quickError = 'حدث خطأ، حاول مرة أخرى.';
                    } finally {
                        this.quickLoading = false;
                    }
                }
             }">

            
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">
                    الخدمات المقدمة
                    <span class="text-gray-400 font-normal text-xs">(اختياري)</span>
                </label>
                <button type="button" @click="addService()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                               text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة خدمة
                </button>
            </div>

            
            <div class="space-y-2" x-show="services.length > 0">
                <template x-for="(svc, index) in services" :key="index">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">

                        
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">الخدمة</label>
                                <select :name="`services[${index}][service_id]`"
                                        x-model="svc.service_id"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">— اختر خدمة —</option>
                                    <template x-for="opt in allServiceOptions" :key="opt.id">
                                        <option :value="String(opt.id)" x-text="opt.name_ar"
                                                :selected="String(opt.id) === String(svc.service_id)"></option>
                                    </template>
                                </select>
                            </div>
                            <button type="button"
                                    @click="removeService(index)"
                                    class="mt-5 w-8 h-8 flex-shrink-0 flex items-center justify-center
                                           text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">القيمة</label>
                                <input type="number"
                                       :name="`services[${index}][amount]`"
                                       x-model="svc.amount"
                                       min="0" step="0.01"
                                       placeholder="0.00"
                                       class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">النوع</label>
                                <div class="flex gap-2">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" :name="`services[${index}][type]`"
                                               value="income" x-model="svc.type" class="sr-only">
                                        <div class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border-2
                                                    text-xs font-medium transition cursor-pointer"
                                             :class="svc.type === 'income'
                                                 ? 'border-green-500 bg-green-50 text-green-700'
                                                 : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                      d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                            </svg>
                                            دخل
                                        </div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" :name="`services[${index}][type]`"
                                               value="expense" x-model="svc.type" class="sr-only">
                                        <div class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border-2
                                                    text-xs font-medium transition cursor-pointer"
                                             :class="svc.type === 'expense'
                                                 ? 'border-red-500 bg-red-50 text-red-700'
                                                 : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                      d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                            </svg>
                                            مصروف
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        
                        <div>
                            <input type="text"
                                   :name="`services[${index}][notes]`"
                                   x-model="svc.notes"
                                   placeholder="ملاحظات (اختياري)..."
                                   class="w-full px-3 py-2 rounded-lg border border-gray-200 text-xs text-gray-600
                                          focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
                        </div>

                        
                        <div class="col-span-12 grid grid-cols-2 gap-3 pt-2 border-t border-gray-100 mt-1">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">المسؤول عن الخدمة</label>
                                <select :name="`services[${index}][team_member_id]`" x-model="svc.team_member_id"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">— بدون تعيين —</option>
                                    <template x-for="member in teamMembers" :key="member.id">
                                        <option :value="String(member.id)" x-text="member.name"
                                                :selected="String(member.id) === String(svc.team_member_id)"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">تكلفته على المشروع</label>
                                <input type="number" :name="`services[${index}][team_cost]`" x-model="svc.team_cost"
                                       min="0" step="0.01" placeholder="0.00"
                                       class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            
            <div x-show="services.length === 0"
                 class="flex flex-col items-center gap-2 py-8 border-2 border-dashed border-gray-200 rounded-xl text-center">
                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400">لا توجد خدمات مضافة بعد</p>
                <button type="button" @click="addService()"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                    + أضف خدمة للمشروع
                </button>
            </div>

            
            <div class="mt-3">
                <button type="button"
                        @click="quickAddOpen = !quickAddOpen"
                        class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-indigo-600 transition">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="quickAddOpen ? 'rotate-45' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-text="quickAddOpen ? 'إلغاء' : 'خدمتك غير موجودة؟ أضفها هنا'"></span>
                </button>

                <div x-show="quickAddOpen" x-transition
                     class="mt-3 p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
                    <p class="text-xs font-medium text-indigo-800 mb-2.5">
                        إضافة خدمة مخصصة
                        <span class="text-indigo-400 font-normal">(ستُحفظ في قائمة خدماتك)</span>
                    </p>
                    <div class="flex gap-2">
                        <input type="text"
                               x-model="quickName"
                               @keydown.enter.prevent="submitQuick()"
                               placeholder="مثال: تصوير منتجات، تدريب، استشارة..."
                               class="flex-1 px-3 py-2 rounded-lg border border-indigo-200 text-sm bg-white
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <button type="button"
                                @click="submitQuick()"
                                :disabled="quickLoading || !quickName.trim()"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium
                                       rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!quickLoading">إضافة</span>
                            <span x-show="quickLoading">...</span>
                        </button>
                    </div>
                    <p x-show="quickError" x-text="quickError"
                       class="mt-1.5 text-xs text-red-600"></p>
                </div>
            </div>

        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($project)): ?>
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
            <div>
                <p class="text-sm font-medium text-gray-900">حالة المشروع</p>
                <p class="text-xs text-gray-400 mt-0.5">المشاريع المتوقفة لا تظهر في إحصاءات لوحة التحكم</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       <?php echo e(old('is_active', $project->is_active) ? 'checked' : ''); ?>

                       class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-500
                            rounded-full peer peer-checked:bg-indigo-600 transition-colors"></div>
                <div class="absolute right-0.5 top-0.5 bg-white w-5 h-5 rounded-full shadow
                            transition-transform peer-checked:translate-x-[-20px]"></div>
            </label>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
            <a href="<?php echo e(route('projects.index')); ?>"
               class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                           text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?php echo e(isset($project) ? 'حفظ التعديلات' : 'إنشاء المشروع'); ?>

            </button>
        </div>

    </div>
</div>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/projects/_form.blade.php ENDPATH**/ ?>