@props(['steps', 'progress', 'completed', 'total'])

<div x-data="{ visible: true }"
     x-show="visible"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="bg-white border border-indigo-100 rounded-2xl overflow-hidden shadow-sm">

    {{-- Header --}}
    <div class="bg-gradient-to-l from-indigo-600 to-violet-600 px-5 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center text-lg">🚀</div>
                <div>
                    <h3 class="text-sm font-bold text-white">ابدأ رحلتك مع دراهم</h3>
                    <p class="text-xs text-indigo-200 mt-0.5">
                        {{ $completed }} من {{ $total }} خطوات مكتملة
                    </p>
                </div>
            </div>

            {{-- زر الإغلاق --}}
            <form method="POST" action="{{ route('onboarding.dismiss') }}" class="inline">
                @csrf
                <button type="submit"
                        @click="visible = false"
                        class="w-7 h-7 bg-white/10 hover:bg-white/20 rounded-lg flex items-center
                               justify-center text-white/70 hover:text-white transition"
                        title="إخفاء">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Progress Bar --}}
        <div class="mt-3">
            <div class="flex justify-between text-xs text-indigo-200 mb-1.5">
                <span>التقدم</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                <div class="h-full bg-white rounded-full transition-all duration-700"
                     style="width: {{ $progress }}%"></div>
            </div>
        </div>
    </div>

    {{-- Steps --}}
    <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($steps as $index => $step)
                <div class="flex items-start gap-3 p-3 rounded-xl
                            {{ $step['completed']
                                ? 'bg-green-50 border border-green-100'
                                : 'bg-gray-50 border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition' }}">

                    {{-- Icon / Checkmark --}}
                    <div class="shrink-0 mt-0.5">
                        @if($step['completed'])
                            <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-8 h-8 bg-white border-2 border-gray-200 rounded-xl flex items-center
                                        justify-center text-base">
                                {{ $step['icon'] }}
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold
                                  {{ $step['completed'] ? 'text-green-700 line-through' : 'text-gray-800' }}">
                            {{ $step['title'] }}
                        </p>
                        @if(!$step['completed'])
                            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                {{ $step['description'] }}
                            </p>
                            <a href="{{ route($step['url_name']) }}"
                               class="inline-flex items-center gap-1 mt-2 text-xs font-medium
                                      text-indigo-600 hover:text-indigo-800 transition">
                                {{ $step['url_label'] }}
                                <svg class="w-3 h-3 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @else
                            <p class="text-xs text-green-600 mt-0.5 font-medium">✓ مكتمل</p>
                        @endif
                    </div>

                    {{-- Step Number --}}
                    @if(!$step['completed'])
                        <span class="shrink-0 text-xs text-gray-300 font-mono mt-0.5">
                            {{ $index + 1 }}/{{ count($steps) }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- All done message --}}
        @if($progress === 100)
            <div class="mt-3 p-3 bg-green-50 border border-green-100 rounded-xl text-center">
                <p class="text-sm font-semibold text-green-700">🎉 أحسنت! أكملت جميع الخطوات</p>
                <p class="text-xs text-green-600 mt-0.5">حسابك جاهز بالكامل. استمتع بـ دراهم!</p>
            </div>
        @endif
    </div>
</div>
