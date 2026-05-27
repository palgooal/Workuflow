<?php $__env->startSection('title', $client->name); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5" x-data="clientProfile()">

    
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('clients.index')); ?>"
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold flex-shrink-0
                        <?php echo e($client->is_archived ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-700'); ?>">
                <?php echo e(mb_substr($client->name, 0, 1)); ?>

            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900"><?php echo e($client->name); ?></h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->status): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($client->status->badgeClass()); ?>">
                        <?php echo e($client->status->label()); ?>

                    </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->is_archived): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                        📦 مؤرشف
                    </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-3 mt-1 flex-wrap">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->company): ?>
                    <span class="text-sm text-gray-500"><?php echo e($client->company); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->email): ?>
                    <a href="mailto:<?php echo e($client->email); ?>" class="text-sm text-indigo-600 hover:underline"><?php echo e($client->email); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->phone): ?>
                    <a href="tel:<?php echo e($client->phone); ?>" class="text-sm text-gray-500 hover:text-gray-700"><?php echo e($client->phone); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->tags->isNotEmpty()): ?>
                <div class="flex flex-wrap gap-1 mt-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $client->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white"
                          style="background-color: <?php echo e($tag->color ?? '#6366f1'); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tag->icon): ?><?php echo e($tag->icon); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php echo e($tag->name); ?>

                    </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="flex items-center gap-2 flex-shrink-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($tagSuggestions) && count($tagSuggestions) > 0): ?>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-amber-700 bg-amber-50
                               border border-amber-200 rounded-xl hover:bg-amber-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    اقتراحات وسوم (<?php echo e(count($tagSuggestions)); ?>)
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                     class="absolute left-0 mt-1 w-56 bg-white border border-gray-100 rounded-xl shadow-lg z-10 p-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tagSuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <form method="POST" action="<?php echo e(route('clients.tags.assign', [$client->public_id, $suggestion->id])); ?>"
                          @submit.prevent="
                            fetch($el.action, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' },
                            })
                            .then(r => r.json())
                            .then(d => { open = false; window.location.reload(); })
                            .catch(() => window.location.reload())
                          ">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                                class="w-full text-right flex items-center gap-2 px-3 py-2 text-sm text-gray-700
                                       hover:bg-gray-50 rounded-lg transition">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                  style="background-color: <?php echo e($suggestion->color ?? '#6366f1'); ?>"></span>
                            تعيين: <?php echo e($suggestion->name); ?>

                        </button>
                    </form>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $client)): ?>
            <a href="<?php echo e(route('clients.edit', $client->public_id)); ?>"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white
                      border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تعديل
            </a>
            <?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->is_archived): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('restore', $client)): ?>
            <form method="POST" action="<?php echo e(route('clients.restore', $client->public_id)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-teal-600
                               bg-teal-50 border border-teal-200 rounded-xl hover:bg-teal-100 transition">
                    استعادة
                </button>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('archive', $client)): ?>
            <form method="POST" action="<?php echo e(route('clients.archive', $client->public_id)); ?>"
                  onsubmit="return confirm('هل تريد أرشفة هذا العميل؟')">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500
                               bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    أرشفة
                </button>
            </form>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">إجمالي الإيراد</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                <?php echo e(number_format($client->total_revenue ?? 0, 0)); ?>

                <span class="text-sm font-normal text-gray-500">₪</span>
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">إجمالي المدفوع</p>
            <p class="text-2xl font-bold text-teal-600 mt-1">
                <?php echo e(number_format($client->total_paid ?? 0, 0)); ?>

                <span class="text-sm font-normal text-gray-500">₪</span>
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">المستحق</p>
            <?php $outstanding = ($client->total_revenue ?? 0) - ($client->total_paid ?? 0) ?>
            <p class="text-2xl font-bold mt-1 <?php echo e($outstanding > 0 ? 'text-red-600' : 'text-gray-900'); ?>">
                <?php echo e(number_format(abs($outstanding), 0)); ?>

                <span class="text-sm font-normal text-gray-500">₪</span>
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">نقاط الصحة</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->health_score !== null): ?>
            <?php
                $score = $client->health_score;
                $color = $score >= 75 ? 'text-teal-600' : ($score >= 50 ? 'text-amber-600' : 'text-red-500');
            ?>
            <p class="text-2xl font-bold <?php echo e($color); ?> mt-1"><?php echo e($score); ?><span class="text-sm font-normal text-gray-500">/100</span></p>
            <?php else: ?>
            <p class="text-2xl font-bold text-gray-300 mt-1">—</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div x-data="{ tab: '<?php echo e(request()->get('tab', 'activity')); ?>' }">

        
        <div class="flex gap-1 border-b border-gray-200 overflow-x-auto">
            <?php
                $tabs = [
                    'activity'   => ['label' => 'النشاط', 'icon' => '📋'],
                    'projects'   => ['label' => 'المشاريع', 'icon' => '📁', 'badge' => $projects->count()],
                    'followups'  => ['label' => 'المتابعات', 'icon' => '⏰'],
                    'info'       => ['label' => 'المعلومات', 'icon' => '📝'],
                ];
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button @click="tab = '<?php echo e($key); ?>'"
                    :class="tab === '<?php echo e($key); ?>'
                        ? 'border-b-2 border-indigo-600 text-indigo-600 font-medium'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2.5 text-sm whitespace-nowrap transition flex items-center gap-1.5">
                <span><?php echo e($tab['icon']); ?></span>
                <?php echo e($tab['label']); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($tab['badge']) && $tab['badge'] > 0): ?>
                <span class="inline-flex items-center justify-center w-4 h-4 text-xs bg-indigo-100 text-indigo-700 rounded-full"><?php echo e($tab['badge']); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div x-show="tab === 'activity'" class="pt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">سجل النشاط</h3>
                    <button onclick="loadTimeline()"
                            class="text-xs text-indigo-600 hover:underline">تحديث</button>
                </div>
                <div id="timeline-container">
                    <div class="flex items-center justify-center py-8 text-gray-400">
                        <svg class="w-5 h-5 animate-spin ml-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        جاري التحميل…
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="tab === 'projects'" class="pt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        المشاريع (<?php echo e($projects->count()); ?>)
                    </h3>
                    <a href="<?php echo e(route('projects.create')); ?>"
                       class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        مشروع جديد
                    </a>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projects->isEmpty()): ?>
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                    <p class="text-sm">لا توجد مشاريع مرتبطة بهذا العميل</p>
                    <a href="<?php echo e(route('projects.create')); ?>"
                       class="mt-3 text-xs text-indigo-600 hover:underline">إضافة مشروع</a>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $profit = $project->netProfit();
                    ?>
                    <a href="<?php echo e(route('projects.show', $project)); ?>"
                       class="flex items-center justify-between p-3 rounded-xl border border-gray-100
                              hover:border-indigo-200 hover:bg-indigo-50/30 transition group">
                        <div class="flex items-center gap-3 min-w-0">
                            
                            <span class="w-3 h-3 rounded-full flex-shrink-0"
                                  style="background-color: <?php echo e($project->color ?? '#6366f1'); ?>"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-700">
                                    <?php echo e($project->name); ?>

                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->type): ?>
                                    <span class="text-xs text-gray-400">
                                        <?php echo e($project->type->label() ?? $project->type->value); ?>

                                    </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="text-xs <?php echo e($project->is_active ? 'text-teal-600' : 'text-gray-400'); ?>">
                                        <?php echo e($project->is_active ? '● نشط' : '○ منتهي'); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 flex-shrink-0 text-left">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->contract_value): ?>
                            <div class="text-right">
                                <p class="text-xs text-gray-400">قيمة العقد</p>
                                <p class="text-sm font-semibold text-gray-700">
                                    <?php echo e(number_format($project->contract_value, 0)); ?> ₪
                                </p>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profit != 0): ?>
                            <div class="text-right">
                                <p class="text-xs text-gray-400">الربح</p>
                                <p class="text-sm font-semibold <?php echo e($profit >= 0 ? 'text-teal-600' : 'text-red-500'); ?>">
                                    <?php echo e(number_format(abs($profit), 0)); ?> ₪
                                </p>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div x-show="tab === 'followups'" class="pt-4 space-y-4">
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $client)): ?>
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">إضافة متابعة جديدة</h3>
                <form method="POST"
                      action="<?php echo e(route('clients.client-follow-ups.store', $client->public_id)); ?>"
                      class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <?php echo csrf_field(); ?>
                    
                    <input type="hidden" name="client_id" value="<?php echo e($client->id); ?>">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">العنوان *</label>
                        <input type="text" name="title" required placeholder="موضوع المتابعة"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الموعد *</label>
                        <input type="datetime-local" name="due_at" required
                               min="<?php echo e(now()->addMinutes(5)->format('Y-m-d\TH:i')); ?>"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الأولوية</label>
                        <select name="priority"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg
                                       focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="1">🔴 عاجل</option>
                            <option value="2">🟠 مرتفع</option>
                            <option value="3" selected>🟡 متوسط</option>
                            <option value="4">🟢 منخفض</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm
                                       font-medium rounded-lg transition">
                            إضافة متابعة
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">المتابعات</h3>
                <?php $followUps = $client->followUps()->orderBy('due_at')->get() ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($followUps->isEmpty()): ?>
                <p class="text-sm text-gray-400 text-center py-6">لا توجد متابعات بعد</p>
                <?php else: ?>
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $followUps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $followUp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        // status هو Enum — نستخدم value للمقارنة
                        $statusVal   = $followUp->status instanceof \App\Modules\CRM\Enums\FollowUpStatus
                                          ? $followUp->status->value
                                          : (string) $followUp->status;
                        $isPast      = $followUp->due_at < now() && $statusVal === 'pending';
                        $isDone      = $statusVal === 'completed';
                        $isCancelled = $statusVal === 'cancelled';
                    ?>
                    <div class="flex items-center justify-between p-3 rounded-lg
                                <?php echo e($isDone ? 'bg-gray-50 opacity-60' : ($isPast ? 'bg-red-50' : 'bg-gray-50')); ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-base">
                                <?php echo e($isDone ? '✅' : ($isCancelled ? '❌' : ($isPast ? '⚠️' : '⏰'))); ?>

                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate
                                          <?php echo e($isDone || $isCancelled ? 'line-through text-gray-400' : ''); ?>">
                                    <?php echo e($followUp->title); ?>

                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($followUp->due_at->format('Y-m-d H:i')); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPast): ?> <span class="text-red-500">(متأخرة)</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isDone && !$isCancelled): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $client)): ?>
                        <div class="flex gap-1 flex-shrink-0">
                            <form method="POST"
                                  action="<?php echo e(route('clients.client-follow-ups.complete', [$client->public_id, $followUp->id])); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                        class="px-2 py-1 text-xs text-teal-600 hover:bg-teal-50 border border-teal-200
                                               rounded-lg transition">
                                    إتمام
                                </button>
                            </form>
                            <form method="POST"
                                  action="<?php echo e(route('clients.client-follow-ups.cancel', [$client->public_id, $followUp->id])); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                        class="px-2 py-1 text-xs text-gray-500 hover:bg-gray-100 border border-gray-200
                                               rounded-lg transition">
                                    إلغاء
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div x-show="tab === 'info'" class="pt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <?php
                        // تحويل Enum إلى label نصي قابل للعرض
                        $sourceLabel = $client->source instanceof \App\Modules\CRM\Enums\ClientSource
                            ? $client->source->label()
                            : $client->source;

                        $fields = [
                            'البريد الإلكتروني' => $client->email,
                            'الهاتف'            => $client->phone,
                            'الشركة'            => $client->company,
                            'المنصب'            => $client->position,
                            'الموقع الإلكتروني' => $client->website,
                            'العنوان'           => $client->address,
                            'المدينة'           => $client->city,
                            'الدولة'            => $client->country,
                            'المصدر'            => $sourceLabel,
                            'تاريخ الإضافة'     => $client->created_at?->format('Y-m-d'),
                            'آخر تواصل'         => $client->last_contact_at?->format('Y-m-d'),
                            'آخر دفعة'          => $client->last_payment_at?->format('Y-m-d'),
                        ];
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($value): ?>
                    <div>
                        <dt class="text-xs font-medium text-gray-500"><?php echo e($label); ?></dt>
                        <dd class="mt-0.5 text-sm text-gray-900">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($value ?? '', 'http')): ?>
                            <a href="<?php echo e($value); ?>" target="_blank" class="text-indigo-600 hover:underline break-all"><?php echo e($value); ?></a>
                            <?php else: ?>
                            <?php echo e($value); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </dd>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->notes): ?>
                    <div class="md:col-span-2">
                        <dt class="text-xs font-medium text-gray-500">ملاحظات</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 whitespace-pre-line"><?php echo e($client->notes); ?></dd>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </dl>
            </div>
        </div>

    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
function clientProfile() {
    return {}
}

// تحميل Timeline عبر AJAX
async function loadTimeline() {
    const container = document.getElementById('timeline-container');
    container.innerHTML = '<div class="flex items-center justify-center py-8 text-gray-400"><svg class="w-5 h-5 animate-spin ml-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>جاري التحميل…</div>';

    try {
        const res  = await fetch('<?php echo e(route('clients.timeline', $client->public_id)); ?>', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (!data.data || data.data.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-400 text-center py-6">لا يوجد نشاط بعد</p>';
            return;
        }

        container.innerHTML = '<div class="relative space-y-0">' +
            data.data.map(item => `
                <div class="flex gap-3 pb-4">
                    <div class="flex-shrink-0 flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-base"
                             style="background-color: ${item.color}20; color: ${item.color}">
                            ${item.icon}
                        </div>
                        <div class="w-px flex-1 bg-gray-100 mt-1"></div>
                    </div>
                    <div class="flex-1 pt-0.5 pb-2">
                        <p class="text-sm text-gray-800">${item.description}</p>
                        <p class="text-xs text-gray-400 mt-0.5">${item.actor} · ${item.occurred_ago}</p>
                    </div>
                </div>
            `).join('') +
        '</div>';
    } catch (e) {
        container.innerHTML = '<p class="text-sm text-red-400 text-center py-6">تعذّر تحميل النشاط</p>';
    }
}

// تحميل التلقائي عند فتح الصفحة
document.addEventListener('DOMContentLoaded', () => loadTimeline());
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/crm/clients/show.blade.php ENDPATH**/ ?>