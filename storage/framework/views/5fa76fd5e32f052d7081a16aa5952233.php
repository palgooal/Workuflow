<?php $__env->startSection('title', 'إدارة العملاء'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5" x-data="clientList()">

    
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">العملاء</h1>
            <p class="mt-0.5 text-sm text-gray-500">إدارة قاعدة عملائك وتتبع علاقاتك التجارية</p>
        </div>
        <div class="flex items-center gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('exportClients', App\Models\Client::class)): ?>
            <a href="<?php echo e(route('clients.export.download', ['format' => 'xlsx'])); ?>"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white border
                      border-gray-200 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                تصدير Excel
            </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('importClients', App\Models\Client::class)): ?>
            <button @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 bg-white border
                           border-gray-200 rounded-xl hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                استيراد Excel
            </button>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Client::class)): ?>
            <a href="<?php echo e(route('clients.create')); ?>"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                عميل جديد
            </a>
            <?php else: ?>
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400
                        text-sm font-medium rounded-xl cursor-not-allowed"
                 title="وصلت للحد الأقصى من العملاء في خطتك">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                الحد الأقصى
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        
        <a href="<?php echo e(route('clients.index')); ?>"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-indigo-200 hover:shadow-sm transition group
                  <?php echo e(!$filters->status && !$filters->isArchived ? 'border-indigo-300 ring-1 ring-indigo-200' : ''); ?>">
            <div class="flex-shrink-0 w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center
                        group-hover:bg-indigo-100 transition">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">الكل</p>
                <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['total'])); ?></p>
            </div>
        </a>

        
        <a href="<?php echo e(route('clients.index', ['status' => 'active'])); ?>"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-teal-200 hover:shadow-sm transition group
                  <?php echo e($filters->status?->value === 'active' ? 'border-teal-300 ring-1 ring-teal-200' : ''); ?>">
            <div class="flex-shrink-0 w-9 h-9 bg-teal-50 rounded-lg flex items-center justify-center
                        group-hover:bg-teal-100 transition">
                <span class="w-2.5 h-2.5 bg-teal-500 rounded-full"></span>
            </div>
            <div>
                <p class="text-xs text-gray-500">نشط</p>
                <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['active'])); ?></p>
            </div>
        </a>

        
        <a href="<?php echo e(route('clients.index', ['status' => 'prospect'])); ?>"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-blue-200 hover:shadow-sm transition group
                  <?php echo e($filters->status?->value === 'prospect' ? 'border-blue-300 ring-1 ring-blue-200' : ''); ?>">
            <div class="flex-shrink-0 w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center
                        group-hover:bg-blue-100 transition">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">محتمل</p>
                <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['prospects'])); ?></p>
            </div>
        </a>

        
        <a href="<?php echo e(route('clients.index', ['has_follow_up' => '1'])); ?>"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-amber-200 hover:shadow-sm transition group
                  <?php echo e($filters->hasPendingFollowUp ? 'border-amber-300 ring-1 ring-amber-200' : ''); ?>">
            <div class="flex-shrink-0 w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center
                        group-hover:bg-amber-100 transition">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">متابعات</p>
                <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['with_follow_up'])); ?></p>
            </div>
        </a>

        
        <a href="<?php echo e(route('clients.index', ['is_archived' => '1'])); ?>"
           class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100
                  hover:border-gray-300 hover:shadow-sm transition group
                  <?php echo e($filters->isArchived ? 'border-gray-300 ring-1 ring-gray-200' : ''); ?>">
            <div class="flex-shrink-0 w-9 h-9 bg-gray-50 rounded-lg flex items-center justify-center
                        group-hover:bg-gray-100 transition">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">مؤرشف</p>
                <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['archived'])); ?></p>
            </div>
        </a>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <form method="GET" action="<?php echo e(route('clients.index')); ?>" class="flex flex-wrap gap-3 items-end">

            
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">بحث</label>
                <div class="relative">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="<?php echo e($filters->search); ?>"
                           placeholder="الاسم، الشركة، البريد، الهاتف…"
                           class="w-full pr-9 pl-4 py-2 text-sm border border-gray-200 rounded-lg
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none">
                </div>
            </div>

            
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                <select name="status"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">الكل</option>
                    <option value="active"   <?php echo e($filters->status?->value === 'active'   ? 'selected' : ''); ?>>نشط</option>
                    <option value="prospect" <?php echo e($filters->status?->value === 'prospect' ? 'selected' : ''); ?>>محتمل</option>
                    <option value="inactive" <?php echo e($filters->status?->value === 'inactive' ? 'selected' : ''); ?>>غير نشط</option>
                    <option value="archived" <?php echo e($filters->status?->value === 'archived' ? 'selected' : ''); ?>>مؤرشف</option>
                </select>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tags->isNotEmpty()): ?>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">الوسم</label>
                <select name="tag_ids[]"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">كل الوسوم</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($tag->id); ?>"
                            <?php echo e(in_array($tag->id, $filters->tagIds) ? 'selected' : ''); ?>>
                        <?php echo e($tag->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">ترتيب حسب</label>
                <select name="sort_by"
                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="created_at"    <?php echo e($filters->sortBy === 'created_at'    ? 'selected' : ''); ?>>تاريخ الإضافة</option>
                    <option value="name"          <?php echo e($filters->sortBy === 'name'          ? 'selected' : ''); ?>>الاسم</option>
                    <option value="health_score"  <?php echo e($filters->sortBy === 'health_score'  ? 'selected' : ''); ?>>نقاط الصحة</option>
                    <option value="total_revenue" <?php echo e($filters->sortBy === 'total_revenue' ? 'selected' : ''); ?>>الإيراد</option>
                    <option value="last_contact_at" <?php echo e($filters->sortBy === 'last_contact_at' ? 'selected' : ''); ?>>آخر تواصل</option>
                </select>
            </div>

            
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الاتجاه</label>
                <select name="sort_dir"
                        class="py-2 px-3 text-sm border border-gray-200 rounded-lg
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="desc" <?php echo e($filters->sortDir === 'desc' ? 'selected' : ''); ?>>↓ تنازلي</option>
                    <option value="asc"  <?php echo e($filters->sortDir === 'asc'  ? 'selected' : ''); ?>>↑ تصاعدي</option>
                </select>
            </div>

            
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm
                               font-medium rounded-lg transition">
                    بحث
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filters->hasFilters()): ?>
                <a href="<?php echo e(route('clients.index')); ?>"
                   class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-lg transition">
                    مسح
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

        </form>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clients->isEmpty()): ?>
        
        <div class="py-16 text-center">
            <svg class="w-14 h-14 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filters->hasFilters()): ?>
            <p class="text-gray-500 font-medium">لا توجد نتائج تطابق الفلاتر المحددة</p>
            <p class="text-sm text-gray-400 mt-1">جرّب تغيير معايير البحث</p>
            <a href="<?php echo e(route('clients.index')); ?>"
               class="inline-block mt-4 text-sm text-indigo-600 hover:underline">مسح الفلاتر</a>
            <?php else: ?>
            <p class="text-gray-500 font-medium">لا يوجد عملاء بعد</p>
            <p class="text-sm text-gray-400 mt-1">ابدأ بإضافة أول عميل لك</p>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Client::class)): ?>
            <a href="<?php echo e(route('clients.create')); ?>"
               class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-indigo-600 text-white
                      text-sm font-medium rounded-xl hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة عميل
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php else: ?>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap">العميل</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden md:table-cell">الحالة</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden lg:table-cell">الوسوم</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden xl:table-cell">الصحة</th>
                        <th class="text-right py-3 px-4 font-medium text-gray-500 whitespace-nowrap hidden xl:table-cell">آخر تواصل</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 transition group">

                        
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                
                                <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                                            <?php echo e($client->is_archived ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-700'); ?>">
                                    <?php echo e(mb_substr($client->name, 0, 1)); ?>

                                </div>
                                <div class="min-w-0">
                                    <a href="<?php echo e(route('clients.show', $client->public_id)); ?>"
                                       class="font-medium text-gray-900 hover:text-indigo-600 transition truncate block">
                                        <?php echo e($client->name); ?>

                                    </a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->company): ?>
                                    <p class="text-xs text-gray-400 truncate"><?php echo e($client->company); ?></p>
                                    <?php elseif($client->email): ?>
                                    <p class="text-xs text-gray-400 truncate"><?php echo e($client->email); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </td>

                        
                        <td class="py-3 px-4 hidden md:table-cell">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->status): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($client->status->badgeClass()); ?>">
                                <?php echo e($client->status->label()); ?>

                            </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>

                        
                        <td class="py-3 px-4 hidden lg:table-cell">
                            <div class="flex flex-wrap gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $client->tags->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium text-white"
                                      style="background-color: <?php echo e($tag->color ?? '#6366f1'); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tag->icon): ?><?php echo e($tag->icon); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php echo e($tag->name); ?>

                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-xs text-gray-300">—</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->tags->count() > 3): ?>
                                <span class="text-xs text-gray-400">+<?php echo e($client->tags->count() - 3); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>

                        
                        <td class="py-3 px-4 hidden xl:table-cell">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->health_score !== null): ?>
                            <?php
                                $score = $client->health_score;
                                $color = $score >= 75 ? 'text-teal-600' : ($score >= 50 ? 'text-amber-600' : 'text-red-500');
                                $bg    = $score >= 75 ? 'bg-teal-50' : ($score >= 50 ? 'bg-amber-50' : 'bg-red-50');
                            ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold
                                         <?php echo e($color); ?> <?php echo e($bg); ?>">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <?php echo e($score); ?>

                            </span>
                            <?php else: ?>
                            <span class="text-xs text-gray-300">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>

                        
                        <td class="py-3 px-4 hidden xl:table-cell">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->last_contact_at): ?>
                            <span class="text-xs text-gray-500"
                                  title="<?php echo e($client->last_contact_at->format('Y-m-d')); ?>">
                                <?php echo e($client->last_contact_at->diffForHumans()); ?>

                            </span>
                            <?php else: ?>
                            <span class="text-xs text-gray-300">لا يوجد</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>

                        
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                                <a href="<?php echo e(route('clients.show', $client->public_id)); ?>"
                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                   title="عرض">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $client)): ?>
                                <a href="<?php echo e(route('clients.edit', $client->public_id)); ?>"
                                   class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                   title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($client->is_archived): ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('restore', $client)): ?>
                                <form method="POST" action="<?php echo e(route('clients.restore', $client->public_id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                            class="p-1.5 text-teal-500 hover:text-teal-700 hover:bg-teal-50 rounded-lg transition"
                                            title="إلغاء الأرشفة">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php else: ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('archive', $client)): ?>
                                <form method="POST" action="<?php echo e(route('clients.archive', $client->public_id)); ?>"
                                      onsubmit="return confirm('هل تريد أرشفة هذا العميل؟')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition"
                                            title="أرشفة">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>

                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                عرض <?php echo e($clients->count()); ?> عميل
            </p>
            <div class="flex gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clients->previousPageUrl()): ?>
                <a href="<?php echo e($clients->previousPageUrl()); ?>"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-600 bg-white
                          border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    السابق
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clients->hasMorePages()): ?>
                <a href="<?php echo e($clients->nextPageUrl()); ?>"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-600 bg-white
                          border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    التالي
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>

</div>


<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('importClients', App\Models\Client::class)): ?>
<div x-data="importManager()"
     x-show="open"
     x-cloak
     @open-import-modal.window="openModal()"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>

    
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto"
         @click.stop>

        
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900">استيراد العملاء</h3>
                <p class="text-xs text-gray-500 mt-0.5">ارفع ملف Excel وسيتم إضافة العملاء تلقائياً</p>
            </div>
            <button @click="closeModal()" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        
        <div class="p-5 space-y-4">

            
            <template x-if="step === 'upload'">
                <div class="space-y-4">

                    
                    <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-sm text-indigo-700 font-medium">نموذج الاستيراد</span>
                        </div>
                        <a href="<?php echo e(route('clients.import.template')); ?>"
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium underline">
                            تحميل القالب (.xlsx)
                        </a>
                    </div>

                    
                    <div class="relative">
                        <label
                            class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer transition"
                            :class="dragover
                                ? 'border-indigo-400 bg-indigo-50'
                                : (file ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50 hover:border-indigo-300 hover:bg-indigo-50')"
                            @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false"
                            @drop.prevent="handleDrop($event)">

                            <template x-if="!file">
                                <div class="text-center">
                                    <svg class="mx-auto w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-sm text-gray-600">اسحب الملف هنا أو <span class="text-indigo-600 font-medium">اضغط للاختيار</span></p>
                                    <p class="text-xs text-gray-400 mt-1">xlsx أو xls أو csv — حتى 10 ميجابايت</p>
                                </div>
                            </template>

                            <template x-if="file">
                                <div class="text-center">
                                    <svg class="mx-auto w-8 h-8 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <p class="text-sm font-medium text-green-700" x-text="file.name"></p>
                                    <p class="text-xs text-green-500 mt-0.5" x-text="formatSize(file.size)"></p>
                                </div>
                            </template>

                            <input type="file" class="hidden" accept=".xlsx,.xls,.csv"
                                   @change="handleFileChange($event)">
                        </label>

                        
                        <template x-if="file">
                            <button @click.prevent="file = null"
                                    class="absolute top-2 left-2 p-1 bg-white rounded-full shadow text-gray-400 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </template>
                    </div>

                    
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" x-model="skipDuplicates"
                                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">تخطي العملاء المكررين (نفس البريد الإلكتروني)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" x-model="updateExisting"
                                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">تحديث العملاء الموجودين (إذا كان البريد مطابقاً)</span>
                        </label>
                    </div>

                    
                    <template x-if="errorMsg">
                        <div class="flex items-center gap-2 p-3 bg-red-50 rounded-xl border border-red-100">
                            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-700" x-text="errorMsg"></p>
                        </div>
                    </template>
                </div>
            </template>

            
            <template x-if="step === 'processing'">
                <div class="space-y-4 py-2">
                    <div class="flex flex-col items-center text-center gap-3">
                        <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800" x-text="statusLabel || 'جارٍ رفع الملف...'"></p>
                            <p class="text-xs text-gray-500 mt-0.5">لا تغلق هذه النافذة</p>
                        </div>
                    </div>

                    
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 bg-indigo-500 rounded-full transition-all duration-500"
                             :style="'width:' + progress + '%'"></div>
                    </div>
                    <p class="text-center text-xs text-gray-400" x-text="progress + '%'"></p>
                </div>
            </template>

            
            <template x-if="step === 'done'">
                <div class="space-y-4 py-2">
                    <div class="flex flex-col items-center text-center gap-3">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center"
                             :class="result.error_count > 0 ? 'bg-amber-50' : 'bg-green-50'">
                            <template x-if="result.error_count === 0">
                                <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <template x-if="result.error_count > 0">
                                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </template>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800"
                               x-text="result.error_count === 0 ? 'تم الاستيراد بنجاح!' : 'اكتمل مع تحذيرات'"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="result.summary || ''"></p>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-green-50 rounded-xl">
                            <p class="text-xl font-bold text-green-600" x-text="result.success_count ?? 0"></p>
                            <p class="text-xs text-green-700">مضاف</p>
                        </div>
                        <div class="text-center p-3 bg-amber-50 rounded-xl">
                            <p class="text-xl font-bold text-amber-600" x-text="result.skipped_count ?? 0"></p>
                            <p class="text-xs text-amber-700">متخطَّى</p>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded-xl">
                            <p class="text-xl font-bold text-red-600" x-text="result.error_count ?? 0"></p>
                            <p class="text-xs text-red-700">أخطاء</p>
                        </div>
                    </div>

                    
                    <template x-if="result.errors && result.errors.length > 0">
                        <div class="max-h-32 overflow-y-auto space-y-1.5 p-3 bg-red-50 rounded-xl border border-red-100">
                            <p class="text-xs font-medium text-red-700 mb-2">تفاصيل الأخطاء:</p>
                            <template x-for="err in result.errors.slice(0, 10)" :key="err.row">
                                <div class="text-xs text-red-600">
                                    <span class="font-medium">سطر <span x-text="err.row"></span>:</span>
                                    <span x-text="Array.isArray(err.errors) ? err.errors.join('، ') : err.errors"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

        </div>

        
        <div class="flex items-center justify-between p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <button @click="closeModal()"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">
                <span x-text="step === 'done' ? 'إغلاق' : 'إلغاء'"></span>
            </button>

            <template x-if="step === 'upload'">
                <button @click="submitImport()"
                        :disabled="!file || uploading"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white
                               bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed
                               rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                    </svg>
                    بدء الاستيراد
                </button>
            </template>

            <template x-if="step === 'done' && result.success_count > 0">
                <a href="<?php echo e(route('clients.index')); ?>"
                   class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white
                          bg-green-600 hover:bg-green-700 rounded-xl transition">
                    عرض العملاء
                </a>
            </template>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
function clientList() {
    return {
        // منطق القائمة — يمكن التوسع مستقبلاً
    }
}

function importManager() {
    return {
        open:          false,
        step:          'upload',   // upload | processing | done
        file:          null,
        dragover:      false,
        uploading:     false,
        skipDuplicates: true,
        updateExisting: false,
        progress:      0,
        statusLabel:   '',
        errorMsg:      '',
        result:        {},
        pollTimer:     null,
        logId:         null,

        openModal() {
            this.reset();
            this.open = true;
        },

        closeModal() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.open = false;
        },

        reset() {
            this.step          = 'upload';
            this.file          = null;
            this.uploading     = false;
            this.progress      = 0;
            this.statusLabel   = '';
            this.errorMsg      = '';
            this.result        = {};
            this.logId         = null;
            if (this.pollTimer) clearInterval(this.pollTimer);
        },

        handleFileChange(event) {
            const f = event.target.files[0];
            if (f) this.validateAndSetFile(f);
        },

        handleDrop(event) {
            this.dragover = false;
            const f = event.dataTransfer.files[0];
            if (f) this.validateAndSetFile(f);
        },

        validateAndSetFile(f) {
            const allowed = ['xlsx', 'xls', 'csv'];
            const ext = f.name.split('.').pop().toLowerCase();
            if (!allowed.includes(ext)) {
                this.errorMsg = 'صيغة الملف غير مدعومة — xlsx أو xls أو csv فقط.';
                return;
            }
            if (f.size > 10 * 1024 * 1024) {
                this.errorMsg = 'حجم الملف يتجاوز 10 ميجابايت.';
                return;
            }
            this.errorMsg = '';
            this.file = f;
        },

        formatSize(bytes) {
            if (bytes < 1024)       return bytes + ' B';
            if (bytes < 1024*1024)  return (bytes/1024).toFixed(1) + ' KB';
            return (bytes/1024/1024).toFixed(1) + ' MB';
        },

        async submitImport() {
            if (!this.file || this.uploading) return;

            this.uploading   = true;
            this.step        = 'processing';
            this.progress    = 10;
            this.statusLabel = 'جارٍ رفع الملف...';
            this.errorMsg    = '';

            const formData = new FormData();
            formData.append('file',             this.file);
            formData.append('skip_duplicates',  this.skipDuplicates ? '1' : '0');
            formData.append('update_existing',  this.updateExisting ? '1' : '0');
            formData.append('_token',           document.querySelector('meta[name=csrf-token]').content);

            try {
                const resp = await fetch('<?php echo e(route('clients.import.store')); ?>', {
                    method: 'POST',
                    body:   formData,
                });

                const json = await resp.json();

                if (!resp.ok) {
                    const msgs = json.errors
                        ? Object.values(json.errors).flat().join(' | ')
                        : (json.message || 'فشل الرفع');
                    this.step     = 'upload';
                    this.errorMsg = msgs;
                    this.uploading = false;
                    return;
                }

                this.logId       = json.data?.id;
                this.progress    = 30;
                this.statusLabel = 'جارٍ معالجة الملف...';

                // إذا اكتمل فوراً (sync queue)
                if (['completed','partial','failed'].includes(json.data?.status)) {
                    this.finishWithResult(json.data);
                    return;
                }

                // بدء polling كل 2 ثانية
                this.pollTimer = setInterval(() => this.pollStatus(), 2000);

            } catch (err) {
                this.step      = 'upload';
                this.errorMsg  = 'حدث خطأ غير متوقع، حاول مجدداً.';
                this.uploading = false;
            }
        },

        async pollStatus() {
            if (!this.logId) return;

            try {
                const resp = await fetch(`<?php echo e(url('clients/import')); ?>/${this.logId}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const json = await resp.json();
                const data = json.data;

                // تحديث الـ progress
                if (data.total_rows > 0) {
                    const done = (data.success_count + data.error_count + data.skipped_count);
                    this.progress = Math.min(90, 30 + Math.round((done / data.total_rows) * 60));
                } else {
                    this.progress = Math.min(this.progress + 5, 85);
                }

                this.statusLabel = data.status_label || 'جارٍ المعالجة...';

                if (json.is_finished) {
                    clearInterval(this.pollTimer);
                    this.finishWithResult(data);
                }
            } catch (e) {
                // تجاهل أخطاء الـ polling المؤقتة
            }
        },

        finishWithResult(data) {
            clearInterval(this.pollTimer);
            this.progress  = 100;
            this.result    = data;
            this.step      = 'done';
            this.uploading = false;
        },
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\laragon\www\Workuflow\resources\views/crm/clients/index.blade.php ENDPATH**/ ?>