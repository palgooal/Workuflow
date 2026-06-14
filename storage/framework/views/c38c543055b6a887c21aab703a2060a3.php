
<?php
    $user = auth()->user();
    $showOnboarding = app(\App\Services\OnboardingService::class)->shouldShow($user);
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showOnboarding): ?>
<div
    x-data="{
        open: false,
        step: 1,
        totalSteps: 5,
        userType: null,
        init() {
            try {
                const dismissed = localStorage.getItem('onboarding_dismissed');
                if (dismissed !== '1') { this.open = true; }
                const savedType = localStorage.getItem('onboarding_user_type');
                if (savedType) { this.userType = savedType; }
            } catch(e) { this.open = true; }
        },
        setUserType(type) {
            this.userType = type;
            try { localStorage.setItem('onboarding_user_type', type); } catch(e) {}
        },
        dismiss() {
            this.open = false;
            try { localStorage.setItem('onboarding_dismissed', '1'); } catch(e) {}
            const token = document.querySelector('meta[name=csrf-token]')?.content;
            if (token) {
                const data = new FormData();
                data.append('_token', token);
                navigator.sendBeacon('<?php echo e(route('onboarding.dismiss')); ?>', data);
            }
        },
        goToPage(url) {
            this.dismiss();
            window.location.href = url;
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
>
    
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="dismiss()"></div>

    
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden"
    >
        
        <div class="h-1.5 bg-gray-100 w-full">
            <div class="h-1.5 bg-indigo-500 transition-all duration-500 rounded-full"
                 :style="`width: ${(step / totalSteps) * 100}%`"></div>
        </div>

        
        <button @click="dismiss()"
                class="absolute top-4 left-4 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        
        <div class="p-8">

            
            <div x-show="step === 1">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-indigo-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">أهلاً بك في دراهم! 🎉</h2>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        سنساعدك على الإعداد خلال دقيقتين — ما الذي يصفك أكثر؟
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="setUserType('freelancer')"
                        :class="userType === 'freelancer'
                            ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-400'
                            : 'border-gray-200 bg-gray-50 hover:border-indigo-300 hover:bg-indigo-50/50'"
                        class="p-4 rounded-2xl border-2 text-center transition cursor-pointer"
                    >
                        <span class="text-3xl block mb-2">💻</span>
                        <p class="text-sm font-semibold text-gray-800">مستقل</p>
                        <p class="text-xs text-gray-500 mt-0.5">فريلانسر أو عمل حر</p>
                    </button>
                    <button
                        @click="setUserType('business')"
                        :class="userType === 'business'
                            ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-400'
                            : 'border-gray-200 bg-gray-50 hover:border-indigo-300 hover:bg-indigo-50/50'"
                        class="p-4 rounded-2xl border-2 text-center transition cursor-pointer"
                    >
                        <span class="text-3xl block mb-2">🏢</span>
                        <p class="text-sm font-semibold text-gray-800">شركة صغيرة</p>
                        <p class="text-xs text-gray-500 mt-0.5">فريق أو نشاط تجاري</p>
                    </button>
                </div>
            </div>

            
            <div x-show="step === 2">
                <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mb-4">
                    <span class="text-2xl">📁</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">أنشئ مشروعك الأول</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">
                    المشروع هو قلب دراهم — كل دخل ومصروف يرتبط بمشروع ليعطيك صورة مالية واضحة.
                </p>
                <div class="space-y-2 mb-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['text-blue-600',   'bg-blue-50',   'اسم المشروع والعملة والعميل'],
                        ['text-purple-600', 'bg-purple-50', 'ربط المعاملات والفواتير به'],
                        ['text-orange-600', 'bg-orange-50', 'تتبع الأرباح والتكاليف تلقائياً'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$tc, $bg, $text]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-2.5 p-2.5 <?php echo e($bg); ?> rounded-xl">
                        <svg class="w-4 h-4 <?php echo e($tc); ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-sm text-gray-700"><?php echo e($text); ?></p>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button
                    @click="goToPage('<?php echo e(route('projects.create')); ?>')"
                    class="flex items-center justify-center gap-2 w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    أنشئ مشروعك الأول الآن
                </button>
                <button @click="step++" class="w-full text-sm text-gray-400 hover:text-gray-600 py-2.5 mt-1 transition">
                    تخطّي هذه الخطوة ←
                </button>
            </div>

            
            <div x-show="step === 3">
                <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-4">
                    <span class="text-2xl">💸</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">سجّل أول معاملة</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-4">
                    استلمت دفعة أو دفعت مقابل خدمة؟ سجّلها الآن وابدأ بتتبع ماليتك من اللحظة الأولى.
                </p>
                <div class="grid grid-cols-2 gap-2 mb-5">
                    <div class="flex items-center gap-2 p-2.5 bg-green-50 rounded-xl">
                        <div class="w-8 h-8 bg-green-200 rounded-lg flex items-center justify-center font-bold text-green-800 shrink-0">↑</div>
                        <div>
                            <p class="text-xs font-semibold text-green-900">دخل</p>
                            <p class="text-xs text-green-700">دفعة من عميل</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 p-2.5 bg-red-50 rounded-xl">
                        <div class="w-8 h-8 bg-red-200 rounded-lg flex items-center justify-center font-bold text-red-800 shrink-0">↓</div>
                        <div>
                            <p class="text-xs font-semibold text-red-900">مصروف</p>
                            <p class="text-xs text-red-700">أداة أو خدمة</p>
                        </div>
                    </div>
                </div>
                <button
                    @click="goToPage('<?php echo e(route('transactions.create')); ?>')"
                    class="flex items-center justify-center gap-2 w-full px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    سجّل أول معاملة الآن
                </button>
                <button @click="step++" class="w-full text-sm text-gray-400 hover:text-gray-600 py-2.5 mt-1 transition">
                    تخطّي هذه الخطوة ←
                </button>
            </div>

            
            <div x-show="step === 4">
                <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mb-4">
                    <span class="text-2xl">👥</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">أضف عميلك الأول</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-4">
                    أضف بيانات عميلك مرة واحدة — أرسل فواتير، تواصل عبر واتساب، واربط المشاريع بنقرة.
                </p>
                <div class="space-y-2 mb-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                        ['text-indigo-600', 'bg-indigo-50', 'الاسم ورقم الواتساب والإيميل'],
                        ['text-purple-600', 'bg-purple-50', 'إصدار فواتير احترافية بنقرة'],
                        ['text-pink-600',   'bg-pink-50',   'تتبع المدفوعات والمستحقات'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$tc, $bg, $text]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-2.5 p-2.5 <?php echo e($bg); ?> rounded-xl">
                        <svg class="w-4 h-4 <?php echo e($tc); ?> shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-sm text-gray-700"><?php echo e($text); ?></p>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button
                    @click="goToPage('<?php echo e(route('clients.create')); ?>')"
                    class="flex items-center justify-center gap-2 w-full px-5 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    أضف عميلك الأول الآن
                </button>
                <button @click="step++" class="w-full text-sm text-gray-400 hover:text-gray-600 py-2.5 mt-1 transition">
                    تخطّي هذه الخطوة ←
                </button>
            </div>

            
            <div x-show="step === 5">
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">لوحتك جاهزة! 🚀</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-6">
                        يمكنك متابعة الإعداد في أي وقت من الـ Checklist في لوحة التحكم.
                    </p>
                    <div class="space-y-2">
                        <a href="<?php echo e(route('projects.create')); ?>"
                           @click="dismiss()"
                           class="flex items-center justify-center gap-2 w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            أنشئ مشروعك الأول
                        </a>
                        <button @click="dismiss()"
                                class="w-full px-5 py-2.5 text-gray-500 hover:text-gray-700 text-sm transition">
                            تخطّي — سأستكشف بنفسي
                        </button>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="px-8 pb-6 flex items-center justify-between" x-show="step < 5">

            
            <button
                @click="step > 1 ? step-- : null"
                :class="step === 1 ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition"
            >
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                السابق
            </button>

            
            <div class="flex items-center gap-1.5">
                <template x-for="i in totalSteps" :key="i">
                    <div class="rounded-full transition-all duration-300"
                         :class="i === step ? 'w-5 h-2 bg-indigo-600' : 'w-2 h-2 bg-gray-200'"></div>
                </template>
            </div>

            
            <button x-show="step === 1"
                    @click="step++"
                    class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                التالي
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="step > 1" class="w-16"></div>

        </div>

    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH F:\laragon\www\Workuflow\resources\views/components/onboarding-modal.blade.php ENDPATH**/ ?>