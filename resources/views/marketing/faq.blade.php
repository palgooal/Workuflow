@extends('layouts.marketing')

@section('title', 'دراهم — الأسئلة الشائعة')

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
          class="max-w-[1104px] bg-gradient-to-r from-white to-g-mint-bright bg-clip-text text-[40px] font-bold leading-[1.15] text-transparent sm:text-[56px] lg:text-[63px] lg:leading-[72px]">
          الأسئلة الشائعة
        </h1>
        <p class="mt-6 text-base leading-8 text-white sm:text-lg">كل ما تحتاج معرفته عن دراهم — في مكان واحد</p>

        <form class="relative mt-6 w-full max-w-[672px]" role="search">
          <label for="faq-search" class="sr-only">ابحث في الأسئلة الشائعة</label>
          <input id="faq-search" type="search" placeholder="كيف يمكننا مساعدتك اليوم؟"
            class="h-14 w-full rounded-full bg-white ps-14 pe-6 text-start text-base text-g-dark shadow-lg outline-none placeholder:text-g-muted focus:ring-4 focus:ring-white/25" />
          <svg class="pointer-events-none absolute start-5 top-1/2 size-[18px] -translate-y-1/2 text-g-green"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
          </svg>
        </form>
      </div>
    </section>

    <!-- Section: Main FAQ -->
    <section class="bg-g-light px-5 py-24">
      <div class="mx-auto grid max-w-[1150px] grid-cols-1 gap-12 lg:grid-cols-[256px_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-28 lg:self-start">
          <nav class="rounded-xl bg-g-light p-2">
            <a href="#general"
              class="faq-sidebar-link flex items-center justify-between rounded-xl border-s-4 border-g-green-lt bg-white p-4 text-base font-bold text-g-purple shadow-sm transition-colors">
              <span>عام</span>
              <svg class="w-3.5 h-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
              </svg>
            </a>
            <a href="#features"
              class="faq-sidebar-link flex items-center justify-between rounded-xl p-4 text-base text-g-body hover:bg-white hover:text-g-purple transition-colors">
              <span>الميزات</span>
              <svg class="w-3.5 h-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
              </svg>
            </a>
            <a href="#pricing"
              class="faq-sidebar-link flex items-center justify-between rounded-xl p-4 text-base text-g-body hover:bg-white hover:text-g-purple transition-colors">
              <span>التسعير</span>
              <svg class="w-3.5 h-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
              </svg>
            </a>
            <a href="#security"
              class="faq-sidebar-link flex items-center justify-between rounded-xl p-4 text-base text-g-body hover:bg-white hover:text-g-purple transition-colors">
              <span>الأمان</span>
              <svg class="w-3.5 h-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
              </svg>
            </a>
            <a href="#support"
              class="faq-sidebar-link flex items-center justify-between rounded-xl p-4 text-base text-g-body hover:bg-white hover:text-g-purple transition-colors">
              <span>الدعم الفني</span>
              <svg class="w-3.5 h-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
              </svg>
            </a>
          </nav>
        </aside>

        <div class="flex flex-col gap-16">
          <section id="general" class="faq-page-section flex flex-col gap-8">
            <h2 class="border-s-4 border-g-green-lt ps-4 text-2xl font-semibold leading-[33.6px] text-g-purple">الأسئلة
              العامة</h2>
            <div class="flex flex-col gap-4">
              <article
                class="faq-page-item cursor-pointer rounded-[14px] border-s-4 border-g-green-lt bg-g-mint-soft p-6 shadow-sm">
                <button type="button"
                  class="faq-page-trigger cursor-pointer flex w-full items-center justify-between gap-4">
                  <span class="text-lg font-bold leading-[30.6px] text-g-purple text-start">ما هو تطبيق دراهم وكيف
                    يعمل؟</span>
                  <svg
                    class="faq-page-icon w-5 h-5 shrink-0 rotate-180 transition-transform duration-300 text-g-green-lt"
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                  </svg>
                </button>
                <div class="faq-page-answer pt-4 text-base leading-6 text-g-body text-start">دراهم هو تطبيق مالي متكامل
                  مصمم خصيصاً للمستقلين والشركات الناشئة في المنطقة. يتيح لك إدارة فواتيرك، تتبع مصاريفك، وربط حساباتك
                  البنكية في منصة واحدة سهلة الاستخدام.</div>
              </article>

              <article class="faq-page-item cursor-pointer rounded-[14px] bg-white p-6 shadow-sm">
                <button type="button"
                  class="faq-page-trigger cursor-pointer flex w-full items-center justify-between gap-4">
                  <span class="text-lg font-bold leading-[30.6px] text-g-purple text-start">هل دراهم متاح في جميع الدول
                    العربية؟</span>
                  <svg class="faq-page-icon w-5 h-5 shrink-0 transition-transform duration-300 text-g-green-lt"
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                  </svg>
                </button>
                <div class="faq-page-answer hidden pt-4 text-base leading-6 text-g-body text-start">نعمل على توسيع
                  التغطية تدريجياً في المنطقة، وتختلف بعض مزايا الربط البنكي والدفع حسب الدولة والأنظمة المحلية.</div>
              </article>
            </div>
          </section>

          <section id="features" class="faq-page-section flex flex-col gap-8">
            <h2 class="border-s-4 border-g-green-lt ps-4 text-2xl font-semibold leading-[33.6px] text-g-purple">الميزات
              والخصائص</h2>
            <div class="flex flex-col gap-4">
              <article
                class="faq-page-item cursor-pointer rounded-[14px] border-s-4 border-g-green-lt bg-g-mint-soft p-6 shadow-sm">
                <button type="button"
                  class="faq-page-trigger cursor-pointer flex w-full items-center justify-between gap-4">
                  <span class="text-lg font-bold leading-[30.6px] text-g-purple text-start">كيف يمكنني أتمتة
                    الفواتير؟</span>
                  <svg
                    class="faq-page-icon w-5 h-5 shrink-0 rotate-180 transition-transform duration-300 text-g-green-lt"
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                  </svg>
                </button>
                <div class="faq-page-answer pt-4 text-base leading-6 text-g-body text-start">من خلال إعداد الفواتير
                  الدورية، يقوم دراهم بإرسال الفواتير تلقائياً لعملائك في المواعيد المحددة مع روابط دفع مباشرة وتذكيرات
                  ذكية.</div>
              </article>

              <article class="faq-page-item cursor-pointer rounded-[14px] bg-white p-6 shadow-sm">
                <button type="button"
                  class="faq-page-trigger cursor-pointer flex w-full items-center justify-between gap-4">
                  <span class="text-lg font-bold leading-[30.6px] text-g-purple text-start">هل يمكنني ربط أكثر من حساب
                    بنكي؟</span>
                  <svg class="faq-page-icon w-5 h-5 shrink-0 transition-transform duration-300 text-g-green-lt"
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                  </svg>
                </button>
                <div class="faq-page-answer hidden pt-4 text-base leading-6 text-g-body text-start">يمكنك ربط حسابات
                  متعددة حسب الخطة المتاحة، ثم متابعة التدفقات النقدية والمصاريف من لوحة واحدة.</div>
              </article>
            </div>
          </section>

          <section id="pricing" class="faq-page-section flex flex-col gap-8">
            <h2 class="border-s-4 border-g-green-lt ps-4 text-2xl font-semibold leading-[33.6px] text-g-purple">التسعير
              والاشتراكات</h2>
            <article
              class="faq-page-item cursor-pointer rounded-[14px] border-s-4 border-g-green-lt bg-g-mint-soft p-6 shadow-sm">
              <button type="button"
                class="faq-page-trigger cursor-pointer flex w-full items-center justify-between gap-4">
                <span class="text-lg font-bold leading-[30.6px] text-g-purple text-start">هل هناك نسخة مجانية؟</span>
                <svg class="faq-page-icon w-5 h-5 shrink-0 rotate-180 transition-transform duration-300 text-g-green-lt"
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
              </button>
              <div class="faq-page-answer pt-4 text-base leading-6 text-g-body text-start">نعم، تتوفر باقة "البداية"
                المجانية للأفراد، والتي تشمل الميزات الأساسية لإدارة الفواتير وتتبع المصاريف المحدودة.</div>
            </article>
          </section>

          <section id="security" class="faq-page-section flex flex-col gap-8">
            <h2 class="border-s-4 border-g-green-lt ps-4 text-2xl font-semibold leading-[33.6px] text-g-purple">الأمان
              والخصوصية</h2>
            <article
              class="faq-page-item cursor-pointer rounded-[14px] border-s-4 border-g-green-lt bg-g-mint-soft p-6 shadow-sm">
              <button type="button"
                class="faq-page-trigger cursor-pointer flex w-full items-center justify-between gap-4">
                <span class="text-lg font-bold leading-[30.6px] text-g-purple text-start">كيف يتم حماية بياناتي
                  المالية؟</span>
                <svg class="faq-page-icon w-5 h-5 shrink-0 rotate-180 transition-transform duration-300 text-g-green-lt"
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
              </button>
              <div class="faq-page-answer pt-4 text-base leading-6 text-g-body text-start">نستخدم تشفير AES-256 بمستوى
                بنكي عالمي، ونلتزم بمعايير SAMA و ISO لضمان أعلى مستويات الأمان والخصوصية لبياناتك.</div>
            </article>
          </section>

          <section id="support" class="faq-page-section flex flex-col gap-8">
            <h2 class="border-s-4 border-g-green-lt ps-4 text-2xl font-semibold leading-[33.6px] text-g-purple">الدعم
              الفني</h2>
            <article
              class="faq-page-item cursor-pointer rounded-[14px] border-s-4 border-g-green-lt bg-g-mint-soft p-6 shadow-sm">
              <button type="button"
                class="faq-page-trigger cursor-pointer flex w-full items-center justify-between gap-4">
                <span class="text-lg font-bold leading-[30.6px] text-g-purple text-start">كيف يمكنني التواصل مع فريق
                  الدعم؟</span>
                <svg class="faq-page-icon w-5 h-5 shrink-0 rotate-180 transition-transform duration-300 text-g-green-lt"
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
              </button>
              <div class="faq-page-answer pt-4 text-base leading-6 text-g-body text-start">فريقنا متاح على مدار الساعة
                عبر الدردشة الحية داخل التطبيق، أو يمكنك مراسلتنا عبر البريد الإلكتروني support@darahum.com.</div>
            </article>
          </section>
        </div>
      </div>
    </section>

    <!-- Section: Support CTA -->
    <section class="bg-white px-5 py-24 text-center">
      <div class="mx-auto flex max-w-[1100px] flex-col items-center gap-10">
        <div class="flex flex-col items-center gap-2">
          <div class="grid size-[45px] place-items-center text-g-green-lt">
            <svg width="45" height="45" viewBox="0 0 45 45" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M22.5 37.5C23.375 37.5 24.1146 37.1979 24.7188 36.5938C25.3229 35.9896 25.625 35.25 25.625 34.375C25.625 33.5 25.3229 32.7604 24.7188 32.1562C24.1146 31.5521 23.375 31.25 22.5 31.25C21.625 31.25 20.8854 31.5521 20.2812 32.1562C19.6771 32.7604 19.375 33.5 19.375 34.375C19.375 35.25 19.6771 35.9896 20.2812 36.5938C20.8854 37.1979 21.625 37.5 22.5 37.5ZM20.25 27.875H24.875C24.875 26.375 25.0417 25.2708 25.375 24.5625C25.7083 23.8542 26.4167 22.9583 27.5 21.875C28.9583 20.4167 29.9896 19.1979 30.5938 18.2188C31.1979 17.2396 31.5 16.125 31.5 14.875C31.5 12.6667 30.75 10.8854 29.25 9.53125C27.75 8.17708 25.7292 7.5 23.1875 7.5C20.8958 7.5 18.9479 8.0625 17.3438 9.1875C15.7396 10.3125 14.625 11.875 14 13.875L18.125 15.5C18.4167 14.375 19 13.4688 19.875 12.7812C20.75 12.0938 21.7708 11.75 22.9375 11.75C24.0625 11.75 25 12.0521 25.75 12.6562C26.5 13.2604 26.875 14.0625 26.875 15.0625C26.875 15.7708 26.6458 16.5208 26.1875 17.3125C25.7292 18.1042 24.9583 18.9792 23.875 19.9375C22.5 21.1458 21.5521 22.3021 21.0312 23.4062C20.5104 24.5104 20.25 26 20.25 27.875ZM5 45C3.625 45 2.44792 44.5104 1.46875 43.5312C0.489583 42.5521 0 41.375 0 40V5C0 3.625 0.489583 2.44792 1.46875 1.46875C2.44792 0.489583 3.625 0 5 0H40C41.375 0 42.5521 0.489583 43.5312 1.46875C44.5104 2.44792 45 3.625 45 5V40C45 41.375 44.5104 42.5521 43.5312 43.5312C42.5521 44.5104 41.375 45 40 45H5ZM5 40H40V5H5V40ZM5 5V40V5Z"
                fill="#13C597" />
            </svg>
          </div>
          <h2 class="pt-2 text-2xl font-semibold leading-[33.6px] text-g-purple">لم تجد إجابتك؟</h2>
          <p class="text-base leading-6 text-g-body">فريقنا المتخصص مستعد دائماً لمساعدتك في أي وقت.</p>
        </div>

        <div class="flex flex-col justify-center gap-4 sm:flex-row">
          <a href="#"
            class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-g-purple px-10 py-[18px] text-base font-bold text-g-purple hover:bg-g-light2 transition-colors">
            <span aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M8 12H12V10H8V12ZM8 9H16V7H8V9ZM8 6H16V4H8V6ZM6 16C5.45 16 4.97917 15.8042 4.5875 15.4125C4.19583 15.0208 4 14.55 4 14V2C4 1.45 4.19583 0.979167 4.5875 0.5875C4.97917 0.195833 5.45 0 6 0H18C18.55 0 19.0208 0.195833 19.4125 0.5875C19.8042 0.979167 20 1.45 20 2V14C20 14.55 19.8042 15.0208 19.4125 15.4125C19.0208 15.8042 18.55 16 18 16H6ZM6 14H18V2H6V14ZM2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4H2V18H16V20H2ZM6 2V14V2Z"
                  fill="#1C0060" />
              </svg>
            </span>
            <span>تصفّح مركز المساعدة</span>
          </a>
          <a href="#"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-g-purple px-10 py-[18px] text-base font-bold text-white hover:opacity-90 transition-opacity">
            <span aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M4 12H12V10H4V12ZM4 9H16V7H4V9ZM4 6H16V4H4V6ZM0 20V2C0 1.45 0.195833 0.979167 0.5875 0.5875C0.979167 0.195833 1.45 0 2 0H18C18.55 0 19.0208 0.195833 19.4125 0.5875C19.8042 0.979167 20 1.45 20 2V14C20 14.55 19.8042 15.0208 19.4125 15.4125C19.0208 15.8042 18.55 16 18 16H4L0 20ZM3.15 14H18V2H2V15.125L3.15 14ZM2 14V2V14Z"
                  fill="white" />
              </svg>
            </span>
            <span>تواصل مع الدعم</span>
          </a>
        </div>
      </div>
    </section>

    <!-- Section: Footer CTA -->
    <section class="relative overflow-hidden bg-gradient-to-r from-g-purple-mid to-g-green-lt px-5 py-24 text-center">
      <div class="absolute -top-32 end-[-128px] size-64 rounded-full bg-white/5 blur-[32px]"></div>
      <div class="absolute -bottom-48 start-[-192px] size-96 rounded-full bg-g-green-lt/5 blur-[32px]"></div>
      <div class="relative mx-auto flex max-w-[1100px] flex-col items-center gap-8">
        <h2 class="text-[34px] font-semibold leading-tight text-white sm:text-[42px] sm:leading-[54.6px]">جاهز لتجربة
          دراهم؟</h2>
        <a href="#"
          class="rounded-full bg-white px-12 py-5 text-xl font-semibold leading-[33.6px] text-g-purple shadow-xl sm:text-2xl hover:bg-g-purple hover:text-white transition-colors">أنشئ
          حسابك المجاني الآن</a>
      </div>
    </section>
  </main>
@endsection

@section('scripts')
<script src="{{ asset('marketing/js/faq.js') }}"></script>
@endsection
