@extends('layouts.app')

@section('title', 'المساعد المالي الذكي')

@section('content')
<div
    class="mx-auto max-w-5xl space-y-6"
    x-data="aiFinancialCopilot()"
    dir="rtl"
>
    <x-page-header
        title="المساعد المالي الذكي"
        subtitle="قراءة آمنة ومجمعة لبياناتك المالية، مع ملاحظات عملية منفصلة لكل عملة."
    />

    <section class="dash-card overflow-hidden" aria-labelledby="copilot-intro-title">
        <div class="grid lg:grid-cols-[1fr_auto] lg:items-center gap-6 p-5 sm:p-7">
            <div class="max-w-2xl">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75L11.1 7.4a2.25 2.25 0 001.33 1.33l3.65 1.35-3.65 1.35a2.25 2.25 0 00-1.33 1.33l-1.35 3.65-1.35-3.65a2.25 2.25 0 00-1.33-1.33L3.42 10.08l3.65-1.35A2.25 2.25 0 008.4 7.4l1.35-3.65z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 id="copilot-intro-title" class="text-base font-bold text-ink">حلّل وضعك المالي عندما تكون مستعداً</h2>
                        <p class="mt-1 text-sm leading-7 text-muted">
                            يعتمد التحليل على إجماليات حسابك فقط، ولا يغيّر أي معاملة أو فاتورة أو رصيد.
                        </p>
                    </div>
                </div>

                <ul class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-600" aria-label="ضمانات التحليل">
                    <li class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                        قراءة فقط
                    </li>
                    <li class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                        العملات منفصلة
                    </li>
                    <li class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                        لا يتم حفظ نتيجة التحليل
                    </li>
                </ul>
            </div>

            <button
                type="button"
                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-btn bg-brand px-5 py-2.5 text-sm font-semibold text-white transition-colors duration-150 hover:bg-brand-600 active:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 lg:w-auto"
                @click="analyze"
                :disabled="state === 'loading'"
                :aria-busy="state === 'loading'"
                aria-describedby="analysis-help"
            >
                <svg x-show="state !== 'loading'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v14l11-7L8 5z"/>
                </svg>
                <svg x-show="state === 'loading'" x-cloak class="h-4 w-4 animate-spin motion-reduce:animate-none" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 00-9 9h3a6 6 0 016-6V3z"/>
                </svg>
                <span x-text="state === 'loading' ? 'جارٍ إعداد التحليل…' : 'ابدأ التحليل'"></span>
            </button>
        </div>
        <p id="analysis-help" class="border-t border-subtle bg-slate-50 px-5 py-3 text-xs leading-6 text-muted sm:px-7">
            النتائج إرشادية وتعتمد على البيانات المجمعة المتاحة لحظة طلب التحليل، من دون تحويل بين العملات.
        </p>
    </section>

    <div class="sr-only" role="status" aria-live="polite" aria-atomic="true" x-text="liveMessage"></div>

    <section
        x-show="state === 'initial'"
        class="py-8 text-center sm:py-12"
        aria-label="حالة التحليل الأولية"
    >
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500" aria-hidden="true">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 006.5 22H20V5a2 2 0 00-2-2H6.5A2.5 2.5 0 004 5.5v14z"/>
            </svg>
        </div>
        <h2 class="mt-4 text-sm font-semibold text-ink">لم يبدأ التحليل بعد</h2>
        <p class="mx-auto mt-1 max-w-xl text-sm leading-7 text-muted">اضغط «ابدأ التحليل» للحصول على ملخص واضح وإشارات مدعومة ببياناتك المجمعة.</p>
    </section>

    <section x-show="state === 'loading'" x-cloak class="dash-card p-5 sm:p-7" aria-label="جارٍ تحميل التحليل">
        <div class="animate-pulse space-y-5 motion-reduce:animate-none">
            <div class="h-5 w-36 rounded bg-slate-200"></div>
            <div class="space-y-2">
                <div class="h-3 w-full rounded bg-slate-100"></div>
                <div class="h-3 w-4/5 rounded bg-slate-100"></div>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="h-24 rounded-xl bg-slate-100"></div>
                <div class="h-24 rounded-xl bg-slate-100"></div>
            </div>
        </div>
    </section>

    <section
        x-show="isErrorState"
        x-cloak
        class="rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-6"
        role="alert"
        aria-labelledby="analysis-error-title"
    >
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-red-600" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
                </svg>
            </span>
            <div>
                <h2 id="analysis-error-title" class="font-semibold text-red-900" x-text="errorTitle"></h2>
                <p class="mt-1 text-sm leading-7 text-red-800" x-text="errorMessage"></p>
            </div>
        </div>
    </section>

    <section
        x-show="result !== null"
        x-cloak
        x-ref="results"
        tabindex="-1"
        class="dash-card overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40"
        aria-labelledby="analysis-result-title"
    >
        <header class="p-5 sm:p-7" :class="healthSurfaceClass">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold" :class="healthBadgeClass" x-text="healthLabel"></span>
                <span class="text-xs text-muted">نتيجة التحليل الحالية</span>
            </div>
            <h2 id="analysis-result-title" class="mt-4 text-lg font-bold text-ink">ملخص الحالة المالية</h2>
            <p class="mt-2 max-w-3xl text-sm leading-8 text-slate-700" x-text="result?.summary_ar"></p>
        </header>

        <div class="divide-y divide-subtle">
            <section x-show="result?.insights.length" class="p-5 sm:p-7" aria-labelledby="insights-title">
                <h3 id="insights-title" class="text-base font-bold text-ink">الإشارات المالية</h3>
                <div class="mt-4 space-y-3">
                    <template x-for="(insight, index) in (result?.insights ?? [])" :key="`${index}-${insight.currency ?? 'quality'}-${insight.evidence_codes.join('-')}`">
                        <article class="rounded-xl bg-slate-50 p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="me-auto text-sm font-semibold text-ink" x-text="insight.title_ar"></h4>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold" :class="severityClass(insight.severity)" x-text="severityLabel(insight.severity)"></span>
                                <span x-show="insight.currency" class="rounded-md bg-white px-2 py-1 text-[11px] font-bold text-slate-700 ring-1 ring-inset ring-slate-200">
                                    <bdi dir="ltr" x-text="insight.currency"></bdi>
                                </span>
                            </div>
                            <p class="mt-2 text-sm leading-7 text-slate-600" x-text="insight.explanation_ar"></p>
                        </article>
                    </template>
                </div>
            </section>

            <section x-show="result?.actions.length" class="p-5 sm:p-7" aria-labelledby="actions-title">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 id="actions-title" class="text-base font-bold text-ink">إرشادات عملية مقترحة</h3>
                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold text-brand">للقراءة فقط</span>
                </div>
                <ol class="mt-4 space-y-4">
                    <template x-for="(action, index) in (result?.actions ?? [])" :key="`${index}-${action.priority}`">
                        <li class="flex gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-bold text-brand nums" x-text="index + 1"></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-semibold text-ink" x-text="action.title_ar"></h4>
                                    <span class="text-[11px] font-medium text-muted" x-text="priorityLabel(action.priority)"></span>
                                </div>
                                <p class="mt-1 text-sm leading-7 text-slate-600" x-text="action.rationale_ar"></p>
                            </div>
                        </li>
                    </template>
                </ol>
            </section>

            <section x-show="result?.limitations_ar.length" class="bg-amber-50/70 p-5 sm:p-7" aria-labelledby="limitations-title">
                <h3 id="limitations-title" class="text-sm font-bold text-amber-900">حدود التحليل</h3>
                <ul class="mt-2 list-disc space-y-1.5 pr-5 text-sm leading-7 text-amber-900">
                    <template x-for="(limitation, index) in (result?.limitations_ar ?? [])" :key="index">
                        <li x-text="limitation"></li>
                    </template>
                </ul>
            </section>

            <footer class="bg-slate-50 px-5 py-4 text-xs leading-6 text-muted sm:px-7" x-text="result?.disclaimer_ar"></footer>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function aiFinancialCopilot() {
    return {
        state: 'initial',
        result: null,
        liveMessage: 'التحليل جاهز للبدء.',

        get isErrorState() {
            return ['rate_limited', 'service_unavailable', 'failure'].includes(this.state);
        },

        get errorTitle() {
            return {
                rate_limited: 'تم بلوغ حد التحليل المؤقت',
                service_unavailable: 'الخدمة غير متاحة الآن',
                failure: 'تعذر إكمال التحليل',
            }[this.state] ?? '';
        },

        get errorMessage() {
            return {
                rate_limited: 'يمكن إجراء خمسة تحليلات كل ساعة. يرجى المحاولة بعد انتهاء المهلة.',
                service_unavailable: 'تعذر الوصول إلى خدمة التحليل حالياً. يرجى المحاولة لاحقاً.',
                failure: 'حدث خطأ غير متوقع. لم يتم تغيير أي من سجلاتك المالية.',
            }[this.state] ?? '';
        },

        get healthLabel() {
            return {
                stable: 'مستقرة',
                attention: 'تحتاج إلى انتباه',
                critical: 'حرجة',
                insufficient_data: 'بيانات غير كافية',
            }[this.result?.health_status] ?? '';
        },

        get healthSurfaceClass() {
            return {
                stable: 'bg-emerald-50',
                attention: 'bg-amber-50',
                critical: 'bg-red-50',
                insufficient_data: 'bg-slate-50',
            }[this.result?.health_status] ?? 'bg-slate-50';
        },

        get healthBadgeClass() {
            return {
                stable: 'bg-emerald-100 text-emerald-800',
                attention: 'bg-amber-100 text-amber-900',
                critical: 'bg-red-100 text-red-800',
                insufficient_data: 'bg-slate-200 text-slate-700',
            }[this.result?.health_status] ?? 'bg-slate-200 text-slate-700';
        },

        severityLabel(severity) {
            return { info: 'معلومة', warning: 'تحذير', critical: 'حرجة' }[severity] ?? '';
        },

        severityClass(severity) {
            return {
                info: 'bg-blue-100 text-blue-800',
                warning: 'bg-amber-100 text-amber-900',
                critical: 'bg-red-100 text-red-800',
            }[severity] ?? 'bg-slate-200 text-slate-700';
        },

        priorityLabel(priority) {
            return { low: 'أولوية منخفضة', medium: 'أولوية متوسطة', high: 'أولوية مرتفعة' }[priority] ?? '';
        },

        normalize(payload) {
            const statuses = ['stable', 'attention', 'critical', 'insufficient_data'];
            if (!payload || !statuses.includes(payload.health_status) || typeof payload.summary_ar !== 'string'
                || !Array.isArray(payload.insights) || !Array.isArray(payload.actions)
                || !Array.isArray(payload.limitations_ar) || typeof payload.disclaimer_ar !== 'string') {
                throw new Error('invalid-result');
            }

            return {
                health_status: payload.health_status,
                summary_ar: payload.summary_ar,
                insights: payload.insights.map((insight) => ({
                    title_ar: insight.title_ar,
                    explanation_ar: insight.explanation_ar,
                    severity: insight.severity,
                    currency: insight.currency,
                    evidence_codes: Array.isArray(insight.evidence_codes) ? insight.evidence_codes : [],
                })),
                actions: payload.actions.map((action) => ({
                    title_ar: action.title_ar,
                    rationale_ar: action.rationale_ar,
                    priority: action.priority,
                })),
                limitations_ar: payload.limitations_ar.filter((item) => typeof item === 'string'),
                disclaimer_ar: payload.disclaimer_ar,
            };
        },

        async analyze() {
            if (this.state === 'loading') return;

            this.state = 'loading';
            this.result = null;
            this.liveMessage = 'جارٍ إعداد التحليل المالي.';

            try {
                const response = await fetch(@js(route('ai-copilot.analyze')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({}),
                });

                if (response.status === 429) {
                    this.state = 'rate_limited';
                    this.liveMessage = this.errorMessage;
                    return;
                }

                if (response.status === 503) {
                    this.state = 'service_unavailable';
                    this.liveMessage = this.errorMessage;
                    return;
                }

                if (!response.ok) {
                    throw new Error('request-failed');
                }

                this.result = this.normalize(await response.json());
                this.state = this.result.health_status;
                this.liveMessage = `اكتمل التحليل. الحالة ${this.healthLabel}.`;
                this.$nextTick(() => this.$refs.results?.focus());
            } catch (error) {
                this.state = 'failure';
                this.result = null;
                this.liveMessage = this.errorMessage;
            }
        },
    };
}
</script>
@endpush
