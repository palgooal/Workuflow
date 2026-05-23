{{--
    Onboarding Modal — يظهر للمستخدمين الجدد الذين لم يُغلقوه بعد
    يُعرض فقط إذا كان onboarding_dismissed_at = null
--}}
@php
    $user = auth()->user();
    $showOnboarding = ! $user->onboarding_dismissed_at
                   && $user->created_at->gt(now()->subDays(7));
@endphp
@if($showOnboarding)
<div
    x-data="{
        open: false,
        step: 1,
        totalSteps: 5,
        init() {
            // تحقق من localStorage أولاً (النسخة الاحتياطية الفورية)
            if (localStorage.getItem('onboarding_dismissed') !== '1') {
                this.open = true;
            }
        },
        dismiss() {
            this.open = false;
            // حفظ فوري في المتصفح — يعمل حتى لو فشل الطلب للسيرفر
            try { localStorage.setItem('onboarding_dismissed', '1'); } catch(e) {}
            // إرسال للسيرفر باستخدام sendBeacon — مضمون الوصول حتى عند تغيير الصفحة
            const token = document.querySelector('meta[name=csrf-token]')?.content;
            if (token) {
                const data = new FormData();
                data.append('_token', token);
                navigator.sendBeacon('{{ route('onboarding.dismiss') }}', data);
            }
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="dismiss()"
    ></div>

    {{-- Modal --}}
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
        {{-- شريط التقدم العلوي --}}
        <div class="h-1.5 bg-gray-100 w-full">
            <div
                class="h-1.5 bg-indigo-500 transition-all duration-500 rounded-full"
                :style="`width: ${(step / totalSteps) * 100}%`"
            ></div>
        </div>

        {{-- زر الإغلاق --}}
        <button
            @click="dismiss()"
            class="absolute top-4 left-4 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- محتوى الخطوات --}}
        <div class="p-8">

            {{-- الخطوة 1: الترحيب --}}
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
                        نظام مالي ذكي لإدارة مشاريعك ومعاملاتك وفريقك في مكان واحد.
                        سنأخذك في جولة سريعة خلال دقيقتين.
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    @foreach([['📁','مشاريع'],['💸','معاملات'],['📊','تقارير']] as [$emoji, $label])
                    <div class="p-3 bg-gray-50 rounded-xl text-center">
                        <span class="text-2xl">{{ $emoji }}</span>
                        <p class="text-xs text-gray-600 mt-1 font-medium">{{ $label }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- الخطوة 2: المشاريع --}}
            <div x-show="step === 2">
                <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mb-4">
                    <span class="text-2xl">📁</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">ابدأ بإنشاء مشروع</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-4">
                    المشروع هو قلب التطبيق. أنشئ مشروعاً لكل عمل تقوم به — سواء كان تصميم هوية، موقع، أو حتى مشروع شخصي.
                </p>
                <div class="space-y-2.5">
                    @foreach([
                        ['اسم المشروع والعملة المستخدمة', 'text-blue-600', 'bg-blue-50'],
                        ['ربطه بعميل من قائمة عملائك', 'text-green-600', 'bg-green-50'],
                        ['إضافة الخدمات التي ستقدمها', 'text-purple-600', 'bg-purple-50'],
                        ['تحديد قيمة العقد وميزانية التكاليف', 'text-orange-600', 'bg-orange-50'],
                    ] as [$text, $tc, $bg])
                    <div class="flex items-center gap-2.5 p-2.5 {{ $bg }} rounded-xl">
                        <svg class="w-4 h-4 {{ $tc }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-sm text-gray-700">{{ $text }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- الخطوة 3: المعاملات --}}
            <div x-show="step === 3">
                <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mb-4">
                    <span class="text-2xl">💸</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">سجّل دخلك ومصروفاتك</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-4">
                    كل دفعة تستلمها أو تدفعها تُسجَّل كمعاملة مرتبطة بالمشروع المناسب.
                </p>
                <div class="space-y-2">
                    <div class="flex items-center gap-3 p-3 bg-green-50 rounded-xl">
                        <div class="w-9 h-9 bg-green-200 rounded-xl flex items-center justify-center font-bold text-green-800">↑</div>
                        <div>
                            <p class="text-sm font-semibold text-green-900">دخل</p>
                            <p class="text-xs text-green-700">استلمت دفعة من عميل؟ سجّلها كدخل</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-red-50 rounded-xl">
                        <div class="w-9 h-9 bg-red-200 rounded-xl flex items-center justify-center font-bold text-red-800">↓</div>
                        <div>
                            <p class="text-sm font-semibold text-red-900">مصروف</p>
                            <p class="text-xs text-red-700">دفعت مقابل خدمة أو أداة؟ سجّلها كمصروف</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الخطوة 4: العملاء والفريق --}}
            <div x-show="step === 4">
                <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mb-4">
                    <span class="text-2xl">👥</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">أضف عملاءك وفريقك</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-4">
                    وفّر وقتك وحافظ على تنظيم عملك:
                </p>
                <div class="space-y-3">
                    <div class="p-3.5 bg-indigo-50 rounded-xl">
                        <p class="text-sm font-semibold text-indigo-900 mb-1">👥 العملاء</p>
                        <p class="text-xs text-indigo-700">أضف بيانات عملائك مرة واحدة، اربطهم بالمشاريع، وتواصل معهم بالواتساب بنقرة.</p>
                    </div>
                    <div class="p-3.5 bg-purple-50 rounded-xl">
                        <p class="text-sm font-semibold text-purple-900 mb-1">🧑‍💼 الفريق</p>
                        <p class="text-xs text-purple-700">عيّن موظفين أو فريلانسرين على كل خدمة، وسجّل دفعاتهم تلقائياً كمصروف.</p>
                    </div>
                </div>
            </div>

            {{-- الخطوة 5: جاهز! --}}
            <div x-show="step === 5">
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">أنت جاهز! 🚀</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-6">
                        يمكنك دائماً العودة لمركز المساعدة من الشريط الجانبي للاطلاع على شروحات مفصّلة لكل ميزة.
                    </p>
                    <div class="space-y-2">
                        <a
                            href="{{ route('projects.create') }}"
                            @click="dismiss()"
                            class="flex items-center justify-center gap-2 w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            أنشئ مشروعك الأول
                        </a>
                        <button
                            @click="dismiss()"
                            class="w-full px-5 py-2.5 text-gray-500 hover:text-gray-700 text-sm transition"
                        >
                            تخطّي — سأستكشف بنفسي
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- أزرار التنقل --}}
        <div class="px-8 pb-6 flex items-center justify-between" x-show="step < 5">
            {{-- السابق --}}
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

            {{-- نقاط التقدم --}}
            <div class="flex items-center gap-1.5">
                <template x-for="i in totalSteps" :key="i">
                    <div
                        class="rounded-full transition-all duration-300"
                        :class="i === step ? 'w-5 h-2 bg-indigo-600' : 'w-2 h-2 bg-gray-200'"
                    ></div>
                </template>
            </div>

            {{-- التالي --}}
            <button
                @click="step++"
                class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition"
            >
                التالي
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

    </div>
</div>
@endif
