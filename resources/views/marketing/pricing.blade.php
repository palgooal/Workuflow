@extends('layouts.marketing')

@section('title', 'دراهم — الأسعار')

@section('body-class', 'font-sans text-g-body bg-g-light overflow-x-hidden')

@section('content')
<main>

    <!-- Section: Hero -->
    <section class="relative overflow-hidden bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)]">
      <div class="absolute -top-44 end-[-230px] size-[500px] rounded-full bg-g-green opacity-15 blur-[40px]"></div>
      <div class="absolute -bottom-32 start-[-140px] size-[400px] rounded-full bg-g-purple opacity-15 blur-[40px]">
      </div>

      <div
        class="relative mx-auto flex min-h-[520px] max-w-[1142px] flex-col items-center justify-center px-6 py-24 text-center sm:min-h-[595px]">
        <h1
          class="max-w-[1104px] text-[40px] font-bold leading-[1.25] text-white sm:text-[56px] lg:text-[63px] lg:leading-[1.5]">
          <span class="block">إبدأ مجانا</span>
          <span class="block bg-gradient-to-r from-white to-[#63f8c7] bg-clip-text text-transparent">وانتقل عندما تكون
            جاهزا</span>
        </h1>
        <p class="mt-6 text-base leading-8 text-white sm:text-lg">لا بطاقة ائتمان. لا التزامات. خطط مصممة لتنمو معك.</p>

        <!-- Billing toggle: شهري first = right in RTL -->
        <div class="mt-7 inline-flex items-center gap-1 rounded-full bg-white/15 p-1 backdrop-blur-sm">
          <button type="button" data-billing="monthly"
            class="pricing-billing-toggle rounded-full bg-white px-6 py-2 text-sm font-semibold text-g-purple shadow transition-colors">شهري</button>
          <button type="button" data-billing="yearly"
            class="pricing-billing-toggle flex items-center gap-2 rounded-full px-5 py-2 text-sm font-medium text-white/80 transition-colors">
            سنوي
            <span
              class="pricing-save-badge rounded-full bg-g-green-lt/25 px-2.5 py-0.5 text-[11px] font-bold text-g-green-lt">وفّر
              20%</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Section: Pricing Cards -->
    <section id="pricing" class="bg-g-light px-5 py-20">
      <div class="mx-auto max-w-[1164px]">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 md:items-start">

          <!-- Card: البداية -->
          <article
            class="flex flex-col rounded-lg border border-g-border bg-white p-8 transition-shadow hover:shadow-md">
            <div class="border-b border-g-border pb-5">
              <span
                class="inline-flex rounded-full bg-g-light2 px-3 py-1 text-xs font-semibold text-g-body">البداية</span>
              <div class="mt-4 flex items-end gap-2">
                <span class="text-[44px] font-bold leading-none text-g-purple">مجاني</span>
                <span class="mb-1 text-sm text-g-muted">/ شهرياً</span>
              </div>
            </div>
            <ul class="mt-6 flex flex-col gap-3.5 text-base text-g-dark">
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>حتى 3 مشاريع نشطة</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>50 معاملة شهرياً</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>فواتير وعروض أسعار</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>إدارة العملاء (CRM أساسي)</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>دعم عبر البريد</span>
              </li>
            </ul>
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}"
              class="mt-8 block rounded-lg border border-g-border px-4 py-3 text-center text-sm font-bold text-g-purple transition-colors hover:border-g-purple hover:bg-g-light2">اشترك
              الآن</a>
          </article>

          <!-- Card: الإحترافية (featured) -->
          <article
            class="relative flex flex-col rounded-lg border-2 border-g-green bg-white px-8 pb-8 pt-10 shadow-[0_20px_50px_rgba(0,108,81,0.13)] transition-shadow hover:shadow-[0_24px_60px_rgba(0,108,81,0.18)] md:-mt-4">
            <!-- Badge -->
            <div class="absolute -top-[18px] inset-x-0 flex justify-center">
              <span class="rounded-full bg-g-green px-5 py-1.5 text-xs font-bold text-white">🔥 الأكثر طلباً</span>
            </div>
            <div class="border-b border-g-green/25 pb-5">
              <span
                class="inline-flex rounded-full bg-g-green/10 px-3 py-1 text-xs font-semibold text-g-green">الإحترافية</span>
              <div class="mt-4 flex items-end gap-2">
                <span data-plan="pro" class="pricing-price text-[44px] font-bold leading-none text-g-green">17</span>
                <span class="mb-1 text-sm text-g-muted">$ / شهرياً</span>
              </div>
            </div>
            <ul class="mt-6 flex flex-col gap-3.5 text-base text-g-dark">
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>مشاريع غير محدودة</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>1,000 معاملة شهرياً</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>تقارير مالية متقدمة + تصدير Excel/PDF</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>تخصيص هوية الفواتير</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>إدارة الصناديق المالية (Wallets)</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>دعم فني ذو أولوية</span>
              </li>
            </ul>
            <a href="{{ auth()->check() ? route('billing.upgrade') : route('register') }}"
              class="mt-8 block rounded-lg bg-g-green px-4 py-3.5 text-center text-sm font-bold text-white transition-opacity hover:opacity-90">اشترك
              الآن</a>
          </article>

          <!-- Card: الفريق -->
          <article
            class="flex flex-col rounded-lg border-2 border-g-purple/40 bg-white p-8 transition-shadow hover:shadow-md">
            <div class="border-b border-g-purple/20 pb-5">
              <span
                class="inline-flex rounded-full bg-g-purple/10 px-3 py-1 text-xs font-semibold text-g-purple">الأعمال</span>
              <div class="mt-4 flex items-end gap-2">
                <span data-plan="team" class="pricing-price text-[44px] font-bold leading-none text-g-purple">45</span>
                <span class="mb-1 text-sm text-g-muted">$ / شهرياً</span>
              </div>
            </div>
            <ul class="mt-6 flex flex-col gap-3.5 text-base text-g-dark">
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>مشاريع ومعاملات غير محدودة</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>وصول كامل للـ API</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>أعضاء فريق متعددين بصلاحيات مخصصة</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>تتبع الوقت والمصاريف للمشاريع</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>مدير حساب مخصص + أولوية قصوى</span>
              </li>
            </ul>
            <a href="{{ auth()->check() ? route('billing.upgrade') : route('register') }}"
              class="mt-8 block rounded-lg border-2 border-g-purple px-4 py-3 text-center text-sm font-bold text-g-purple transition-colors hover:bg-g-light2">تواصل
              مع المبيعات</a>
          </article>

        </div>
      </div>
    </section>

    <!-- Section: Plan Comparison -->
    <section class="bg-white px-5 py-20">
      <div class="mx-auto max-w-[1150px]">
        <h2 class="mb-12 text-center text-[32px] font-bold text-g-purple sm:text-[40px]">قارن بين الخطط بالتفصيل</h2>
        <div class="overflow-x-auto rounded-2xl border border-g-border">
          <table class="w-full min-w-[680px] border-collapse text-sm">
            <thead>
              <tr>
                <th class="w-[42%] px-6 pt-6 pb-8 text-start text-base font-bold text-g-navy">الميزة</th>
                <th class="px-6 pt-6 pb-8 text-center text-base font-bold text-g-navy">البداية</th>
                <th
                  class="border-t-2 border-g-green bg-g-mint-soft px-6 pt-4 pb-8 text-center text-base font-bold text-g-green">
                  المحترف</th>
                <th class="px-6 pt-6 pb-8 text-center text-base font-bold text-g-navy">الأعمال</th>
              </tr>
            </thead>
            <tbody>

              <!-- إدارة المشاريع -->
              <tr class="bg-g-light2">
                <td colspan="4" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-g-green">
                  إدارة المشاريع</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">عدد المشاريع</td>
                <td class="px-6 py-4 text-center text-g-body">3</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-g-body">غير محدود</td>
                <td class="px-6 py-4 text-center text-g-body">غير محدود</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">المعاملات الشهرية</td>
                <td class="px-6 py-4 text-center text-g-body">50</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-g-body">1,000</td>
                <td class="px-6 py-4 text-center text-g-body">غير محدود</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">تتبع الوقت</td>
                <td class="px-6 py-4 text-center text-base text-g-muted/50">—</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-base text-g-muted/50">—</td>
                <td class="px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
              </tr>

              <!-- العملاء والمبيعات -->
              <tr class="bg-g-light2">
                <td colspan="4" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-g-green">
                  العملاء والمبيعات</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">إدارة العملاء (CRM)</td>
                <td class="px-6 py-4 text-center text-g-body">أساسي</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-g-body">متقدم</td>
                <td class="px-6 py-4 text-center text-g-body">متقدم</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">عروض الأسعار</td>
                <td class="px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
                <td class="px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
              </tr>

              <!-- الفواتير -->
              <tr class="bg-g-light2">
                <td colspan="4" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-g-green">
                  الفواتير</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">قوالب فواتير مخصصة</td>
                <td class="px-6 py-4 text-center text-base text-g-muted/50">—</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
                <td class="px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">ربط بوابات الدفع</td>
                <td class="px-6 py-4 text-center text-g-body">محدود</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-g-body">كامل</td>
                <td class="px-6 py-4 text-center text-g-body">كامل</td>
              </tr>

              <!-- التقارير والمالية -->
              <tr class="bg-g-light2">
                <td colspan="4" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-g-green">
                  التقارير والمالية</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">تصدير التقارير (Excel/PDF)</td>
                <td class="px-6 py-4 text-center text-base text-g-muted/50">—</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
                <td class="px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">الضرائب التلقائية (ZATCA)</td>
                <td class="px-6 py-4 text-center text-base text-g-muted/50">—</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
                <td class="px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
              </tr>

              <!-- API -->
              <tr class="bg-g-light2">
                <td colspan="4" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-g-green">
                  التكاملات</td>
              </tr>
              <tr class="border-b border-g-border/60 transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">وصول API</td>
                <td class="px-6 py-4 text-center text-base text-g-muted/50">—</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-base text-g-muted/50">—</td>
                <td class="px-6 py-4 text-center text-base font-semibold text-g-green">✓</td>
              </tr>
              <!-- الدعم -->
              <tr class="bg-g-light2">
                <td colspan="4" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-g-green">
                  الدعم</td>
              </tr>
              <tr class="transition-colors duration-150 hover:bg-g-light2/70">
                <td class="px-6 py-4 text-start text-g-dark">سرعة الرد</td>
                <td class="px-6 py-4 text-center text-g-body">خلال 48 ساعة</td>
                <td class="bg-g-mint-soft/30 px-6 py-4 text-center text-g-body">خلال 24 ساعة</td>
                <td class="px-6 py-4 text-center text-g-body">أولوية قصوى</td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Section: Trust Badges -->
    <section class="bg-g-light2 px-5 py-10">
      <div class="mx-auto max-w-[1150px]">
        <div class="flex flex-col items-stretch md:flex-row">

          <!-- Item: ضمان استرداد المبلغ -->
          <div class="flex flex-1 items-center justify-center gap-4 px-10 py-7">
            <div class="shrink-0 grid size-11 place-items-center rounded-full bg-white text-g-green">
              <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.95 13.55L12.6 7.9L11.175 6.475L6.95 10.7L4.85 8.6L3.425 10.025L6.95 13.55ZM8 20C5.68333 19.4167 3.77083 18.0875 2.2625 16.0125C0.754167 13.9375 0 11.6333 0 9.1V3L8 0L16 3V9.1C16 11.6333 15.2458 13.9375 13.7375 16.0125C12.2292 18.0875 10.3167 19.4167 8 20Z" fill="#006C51"/>
            </svg>

            </div>
            <div class="flex flex-col gap-1 text-start">
              <span class="text-sm font-bold text-g-navy">ضمان استرداد المبلغ</span>
              <span class="text-sm text-g-muted">استرجع نقودك خلال 30 يوماً</span>
            </div>
          </div>

          <!-- Separator -->
          <div class="hidden md:flex items-center">
            <div class="h-10 w-px bg-g-border2"></div>
          </div>
          <div class="md:hidden h-px w-3/4 self-center bg-g-border2"></div>

          <!-- Item: لا بطاقة ائتمان -->
          <div class="flex flex-1 items-center justify-center gap-4 px-10 py-7">
            <div class="shrink-0 grid size-11 place-items-center rounded-full bg-white text-g-green">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13.2 8.95L8.55 4.3C8.78333 4.2 9.02083 4.125 9.2625 4.075C9.50417 4.025 9.75 4 10 4C10.9833 4 11.8125 4.3375 12.4875 5.0125C13.1625 5.6875 13.5 6.51667 13.5 7.5C13.5 7.75 13.475 7.99583 13.425 8.2375C13.375 8.47917 13.3 8.71667 13.2 8.95V8.95M3.85 15.1C4.7 14.45 5.65 13.9375 6.7 13.5625C7.75 13.1875 8.85 13 10 13C10.3 13 10.5875 13.0125 10.8625 13.0375C11.1375 13.0625 11.425 13.1 11.725 13.15L9.525 10.95C8.74167 10.85 8.07083 10.5208 7.5125 9.9625C6.95417 9.40417 6.625 8.73333 6.525 7.95L3.675 5.1C3.14167 5.78333 2.72917 6.5375 2.4375 7.3625C2.14583 8.1875 2 9.06667 2 10C2 10.9833 2.1625 11.9083 2.4875 12.775C2.8125 13.6417 3.26667 14.4167 3.85 15.1V15.1M16.3 14.9C16.8333 14.2167 17.25 13.4625 17.55 12.6375C17.85 11.8125 18 10.9333 18 10C18 7.78333 17.2208 5.89583 15.6625 4.3375C14.1042 2.77917 12.2167 2 10 2C9.06667 2 8.1875 2.15 7.3625 2.45C6.5375 2.75 5.78333 3.16667 5.1 3.7L16.3 14.9V14.9M10.0125 20C8.6375 20 7.34167 19.7375 6.125 19.2125C4.90833 18.6875 3.84583 17.9708 2.9375 17.0625C2.02917 16.1542 1.3125 15.0917 0.7875 13.875C0.2625 12.6583 0 11.3625 0 9.9875C0 8.6125 0.2625 7.32083 0.7875 6.1125C1.3125 4.90417 2.02917 3.84583 2.9375 2.9375C3.84583 2.02917 4.90833 1.3125 6.125 0.7875C7.34167 0.2625 8.6375 0 10.0125 0C11.3875 0 12.6792 0.2625 13.8875 0.7875C15.0958 1.3125 16.1542 2.02917 17.0625 2.9375C17.9708 3.84583 18.6875 4.90417 19.2125 6.1125C19.7375 7.32083 20 8.6125 20 9.9875C20 11.3625 19.7375 12.6583 19.2125 13.875C18.6875 15.0917 17.9708 16.1542 17.0625 17.0625C16.1542 17.9708 15.0958 18.6875 13.8875 19.2125C12.6792 19.7375 11.3875 20 10.0125 20V20" fill="#006C51"/>
</svg>

            </div>
            <div class="flex flex-col gap-1 text-start">
              <span class="text-sm font-bold text-g-navy">لا بطاقة ائتمان مطلوبة</span>
              <span class="text-sm text-g-muted">ابدأ تجربتك المجانية فوراً</span>
            </div>
            
          </div>

          <!-- Separator -->
          <div class="hidden md:flex items-center">
            <div class="h-10 w-px bg-g-border2"></div>
          </div>
          <div class="md:hidden h-px w-3/4 self-center bg-g-border2"></div>

          <!-- Item: إلغاء في أي وقت -->
          <div class="flex flex-1 items-center justify-center gap-4 px-10 py-7">
                        <div class="shrink-0 grid size-11 place-items-center rounded-full bg-white text-g-green">
              <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.7 16.7L5.3 15.3L7.6 13L5.3 10.7L6.7 9.3L9 11.6L11.3 9.3L12.7 10.7L10.4 13L12.7 15.3L11.3 16.7L9 14.4L6.7 16.7ZM2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4C0 3.45 0.195833 2.97917 0.5875 2.5875C0.979167 2.19583 1.45 2 2 2H3V0H5V2H13V0H15V2H16C16.55 2 17.0208 2.19583 17.4125 2.5875C17.8042 2.97917 18 3.45 18 4V18C18 18.55 17.8042 19.0208 17.4125 19.4125C17.0208 19.8042 16.55 20 16 20H2ZM2 18H16V8H2V18Z" fill="#006C51"/>
</svg>

            </div>
            <div class="flex flex-col gap-1 text-start">
              <span class="text-sm font-bold text-g-navy">إلغاء في أي وقت</span>
              <span class="text-sm text-g-muted">مرونة كاملة في اشتراكك</span>
            </div>

          </div>

        </div>
      </div>
    </section>

    <!-- Section: FAQ -->
    <section id="faq" class="bg-white px-5 py-20">
      <div class="mx-auto max-w-[1000px]">
        <h2 class="mb-12 text-center text-[32px] font-bold text-g-navy sm:text-[40px]">الأسئلة الشائعة</h2>
        <div class="flex flex-col gap-4 md:flex-row md:items-start">

          <!-- Column 1 -->
          <div class="flex flex-1 flex-col gap-4">

            <article class="pricing-faq-item rounded-2xl border-2 border-g-navy bg-white p-6 transition-all duration-200 hover:shadow-md" data-faq-open="true">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">هل يمكنني استخدام دراهم مجاناً للأبد؟</span>
                <span class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-navy/10 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  <svg class="pricing-faq-icon-plus hidden size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
              </button>
              <div class="pricing-faq-answer mt-4 text-sm leading-7 text-g-body">
                نعم، الخطة المجانية دائمة وتغطي احتياجات المستقلين في بداية طريقهم. يمكنك الترقية فقط عندما تحتاج لمميزات إضافية.
              </div>
            </article>

            <article class="pricing-faq-item rounded-2xl border border-g-border bg-white p-6 transition-all duration-200 hover:shadow-md" data-faq-open="false">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">كيف يمكنني التواصل مع الدعم الفني؟</span>
                <span class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-light2 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus hidden size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  <svg class="pricing-faq-icon-plus size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
              </button>
              <div class="pricing-faq-answer hidden mt-4 text-sm leading-7 text-g-body">
                يمكنك التواصل معنا عبر البريد الإلكتروني أو من خلال قنوات الدعم داخل التطبيق حسب خطتك.
              </div>
            </article>

          </div>

          <!-- Column 2 -->
          <div class="flex flex-1 flex-col gap-4">

            <article class="pricing-faq-item rounded-2xl border border-g-border bg-white p-6 transition-all duration-200 hover:shadow-md" data-faq-open="false">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">ماذا يحدث لبياناتي إذا ألغيت اشتراكي؟</span>
                <span class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-light2 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus hidden size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  <svg class="pricing-faq-icon-plus size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
              </button>
              <div class="pricing-faq-answer hidden mt-4 text-sm leading-7 text-g-body">
                تبقى بياناتك قابلة للتصدير، ويمكنك تحميل الفواتير والعملاء والتقارير قبل إغلاق الاشتراك المدفوع.
              </div>
            </article>

            <article class="pricing-faq-item rounded-2xl border border-g-border bg-white p-6 transition-all duration-200 hover:shadow-md" data-faq-open="false">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">هل البرنامج متوافق مع الفاتورة الإلكترونية؟</span>
                <span class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-light2 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus hidden size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  <svg class="pricing-faq-icon-plus size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
              </button>
              <div class="pricing-faq-answer hidden mt-4 text-sm leading-7 text-g-body">
                يدعم دراهم متطلبات الفاتورة الإلكترونية ويتيح إنشاء فواتير منظمة قابلة للمشاركة والتصدير.
              </div>
            </article>

          </div>

        </div>
      </div>
    </section>

    <!-- Section: Final CTA -->
    <section
      class="relative overflow-hidden bg-[linear-gradient(to_right,#310e8e_0%,#13c597_100%)] px-5 py-24 text-center">
      <div class="absolute start-[-200px] top-[-200px] size-[500px] rounded-full border-[30px] border-white/10"></div>
      <div class="absolute end-[-100px] bottom-[-100px] size-[240px] rounded-full border-[20px] border-white/10"></div>

      <div class="relative mx-auto flex max-w-[760px] flex-col items-center gap-4">
        <span class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-[#fcb87d]">الفرصة
          بين يديك</span>
        <h2 class="text-[36px] font-bold leading-tight text-white sm:text-[56px] sm:leading-[70px]">
          جاهز لتنظيم
          <span class="block text-g-orange">حياتك المالية؟</span>
        </h2>
        <p class="max-w-[672px] pt-2 text-base leading-8 text-white/70 sm:text-lg">انضم إلى مستقلين يديرون
          أعمالهم بثقة وسهولة. ابدأ مجاناً — لا بطاقة ائتمان، لا التزامات.</p>
        <div class="mt-8 flex flex-col gap-4 sm:flex-row">
          <a href="{{ auth()->check() ? route('dashboard') : route('register') }}"
            class="rounded bg-g-orange px-10 py-5 text-lg font-bold text-white hover:opacity-90 transition-opacity">ابدأ
            مجاناً</a>
          <a href="#"
            class="rounded border-2 border-white/20 px-10 py-5 text-lg font-bold text-white hover:bg-white/10 transition-colors">احجز
            جلسة استشارية</a>
        </div>
      </div>
    </section>

  </main>
@endsection

@section('scripts')
<script src="{{ asset('marketing/js/pricing.js') }}"></script>
@endsection
