@extends('layouts.marketing')

@section('title', 'دراهم — المنصة المالية للمستقلين')

@section('content')
<main>

    <!-- ════════════════════════════════════════
          Section: Hero
    ════════════════════════════════════════ -->
    <section class="relative bg-g-light overflow-hidden pt-20">

      <!-- Blur circle 1 — #006C51 @ top-end: w:500 h:500 x:739 y:-158 opacity 0.15 blur(80px) -->
      <div
        class="absolute w-[500px] h-[500px] rounded-full bg-g-green blur-[80px] opacity-15 pointer-events-none top-[-158px] end-[-87px]">
      </div>
      <!-- Blur circle 2 — #1C0060 @ bottom-start: w:400 h:400 x:-113 y:461 opacity 0.15 blur(80px) -->
      <div
        class="absolute w-[400px] h-[400px] rounded-full bg-g-purple blur-[80px] opacity-15 pointer-events-none top-[461px] start-[-113px]">
      </div>

      <!-- Container: max-w 1152px py-[159px] -->
      <div class="relative z-10 max-w-[1152px] mx-auto py-[120px] lg:py-[159px]">
        <!--
          2-col layout. In RTL flex-row: first child = inline-start = RIGHT side.
          DOM: [text-col, image-col] → text on RIGHT, image on LEFT ✓
        -->
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16 px-10 md:px-0">

          <!-- ── Text Column (RIGHT in RTL) ────────────────── -->
          <!-- layout_0XA3YJ: column, gap: 22.8px, locationRelativeToParent x:584 -->
          <div class="flex flex-col items-start gap-[23px] w-full lg:w-1/2 order-2 lg:order-1">

            <!-- Heading 1: IBM Plex Sans Arabic Bold 700 63px/72px
                 ts1: dark fill #191C1F
                 ts2: gradient linear-gradient(90deg, #006C51 0%, #1C0060 100%) -->
            <h1 class="font-sans font-bold text-[42px] sm:text-[52px] lg:text-[63px] leading-[1.3] text-start w-full">
              <span class="block text-g-dark">أدر أموالك بذكاء</span>
              <span class="block bg-gradient-to-l from-g-purple to-g-green bg-clip-text text-transparent">كمستقل
                محترف</span>
            </h1>

            <!-- Body: Regular 400 18px/30.6px color #484553 -->
            <p class="text-g-body font-normal text-lg leading-[1.7] text-start max-w-[520px]">
              منصة دراهم تمنحك الأدوات المالية التي تحتاجها لتنمية عملك الحر، من تتبع الفواتير إلى إدارة الضرائب
              والادخار التلقائي، كل ذلك في مكان واحد.
            </p>

            <!-- Buttons row: layout_YM3KIH row justify-start gap-16px pt-[17px] -->
            <div class="flex items-center gap-4 pt-[17px] w-full justify-start flex-wrap">

              <!-- ابدأ تجربتك المجانية: bg #006C51 radius 12px py-[16px] px-[32px] SemiBold 24px white shadow -->
              <a href="#"
                class="relative inline-flex items-center justify-center bg-g-green text-white font-semibold text-[24px] leading-[1.4] rounded-[12px] py-[16px] px-[32px] shadow-[0px_8px_10px_-6px_rgba(0,108,81,0.2),0px_20px_25px_-5px_rgba(0,108,81,0.2)] hover:opacity-90 transition-opacity">
                ابدأ تجربتك المجانية
              </a>

              <!-- شاهد العرض: ghost — icon + text, #1C0060 SemiBold 24px py-5 px-6 -->
              <a href="#"
                class="inline-flex items-center gap-2 text-g-purple font-semibold text-xl leading-[1.4] py-[20px] px-6 hover:opacity-75 transition-opacity">
                <!-- Play icon SVG -->
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M7.5 14.5L14.5 10L7.5 5.5V14.5ZM10 20C8.61667 20 7.31667 19.7375 6.1 19.2125C4.88333 18.6875 3.825 17.975 2.925 17.075C2.025 16.175 1.3125 15.1167 0.7875 13.9C0.2625 12.6833 0 11.3833 0 10C0 8.61667 0.2625 7.31667 0.7875 6.1C1.3125 4.88333 2.025 3.825 2.925 2.925C3.825 2.025 4.88333 1.3125 6.1 0.7875C7.31667 0.2625 8.61667 0 10 0C11.3833 0 12.6833 0.2625 13.9 0.7875C15.1167 1.3125 16.175 2.025 17.075 2.925C17.975 3.825 18.6875 4.88333 19.2125 6.1C19.7375 7.31667 20 8.61667 20 10C20 11.3833 19.7375 12.6833 19.2125 13.9C18.6875 15.1167 17.975 16.175 17.075 17.075C16.175 17.975 15.1167 18.6875 13.9 19.2125C12.6833 19.7375 11.3833 20 10 20ZM10 18C12.2333 18 14.125 17.225 15.675 15.675C17.225 14.125 18 12.2333 18 10C18 7.76667 17.225 5.875 15.675 4.325C14.125 2.775 12.2333 2 10 2C7.76667 2 5.875 2.775 4.325 4.325C2.775 5.875 2 7.76667 2 10C2 12.2333 2.775 14.125 4.325 15.675C5.875 17.225 7.76667 18 10 18Z"
                    fill="#1C0060" />
                </svg>
                شاهد العرض
              </a>

            </div>
          </div><!-- /Text Column -->

          <!-- ── Image Column (LEFT in RTL) ─────────────────── -->
          <!-- layout_5YD8AM: row center, h:500, x:0 y:0 → occupies left half -->
          <div class="relative w-full lg:w-1/2 flex justify-center order-1 lg:order-2">
            <!-- Mockup container: layout_K1TC9S  w:512 -->
            <div class="relative w-full max-w-[512px]">

              <!-- Main dashboard image: w:547.61 h:547.61 rounded-[24px] border-white-8 shadow -->
              <div
                class="relative rounded-[24px] rotate-[-5deg] overflow-hidden border-[8px] border-white shadow-[0px_25px_50px_-12px_rgba(0,0,0,0.25)] w-full aspect-square">
                <img src="{{ asset('marketing/imgs/hero-dashboard-56586a.png') }}" alt="لوحة تحكم دراهم المالية"
                  class="w-full h-full object-cover" />
              </div>

              <!-- Floating Element 1: تم استلام الدفعة + 2,500
                   layout_9AJS07: x:312 y:-40 → near top-end of image
                   row, gap:16, p: 24px 24px 24px 27px
                   bg: rgba(255,255,255,0.7) border: rgba(255,255,255,0.3) radius-24 backdrop-blur-12 shadow -->
              <div data-float="1"
                class="absolute top-[-40px] start-0 lg:start-[-20px] flex items-center gap-4 rounded-[24px] border border-white/30 bg-white/70 backdrop-blur-[12px] shadow-[0px_8px_10px_-6px_rgba(0,0,0,0.1),0px_20px_25px_-5px_rgba(0,0,0,0.1)] ps-[27px] pe-6 py-6">
                <!-- Green circle icon: bg #D1FAE5 p-2 rounded-full -->
                <div class="bg-[#D1FAE5] rounded-full p-2 flex-shrink-0">
                  <svg class="w-5 h-5 text-g-green" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                  </svg>
                </div>
                <!-- Text -->
                <div class="flex flex-col items-start">
                  <span class="font-medium text-[13px] leading-[18px] text-g-body">تم استلام الدفعة</span>
                  <span class="font-bold text-[24px] leading-[34px] text-g-green">+ 2,500 ريال</span>
                </div>
              </div>

              <!-- Floating Element 2: صافي الربح الشهري + 15,400
                   layout_006OAD: x:-48 y:452.41 → bottom-start of image (overflows left by 48px)
                   column, gap:8, p: 23px 24px 24px
                   same visual style -->
              <div data-float="2"
                class="absolute bottom-[-20px] end-0 lg:end-[-48px] flex flex-col gap-2 rounded-[24px] border border-white/30 bg-white/70 backdrop-blur-[12px] shadow-[0px_8px_10px_-6px_rgba(0,0,0,0.1),0px_20px_25px_-5px_rgba(0,0,0,0.1)] py-[23px] ps-6 pe-6">
                <div class="flex flex-col items-start">
                  <span class="font-medium text-[13px] leading-[18px] text-g-body">صافي الربح الشهري</span>
                  <div class="flex items-end gap-2">
                    <span class="font-bold text-[24px] leading-[34px] text-g-purple">15,400</span>
                    <span class="font-medium text-[14px] leading-[21px] text-g-body pb-1">ريال</span>
                  </div>
                </div>
                <!-- Chart icon -->
                <div class="flex items-end justify-start gap-2">
                  <!-- Mini bar chart icon -->
                  <svg width="150" height="6" viewBox="0 0 150 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="150" height="6" rx="3" fill="#EDEEF2" />
                    <rect x="37.5" width="112.5" height="6" rx="3" fill="#006C51" />
                  </svg>

                </div>

              </div>

            </div>
          </div><!-- /Image Column -->

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Stats
    ════════════════════════════════════════ -->
    <section class="bg-white py-16 px-10">
      <div class="max-w-[1280px] mx-auto">
        <!--
          Figma x-positions (LTR): 24/7=0, 99%=296, 200M+=592, +50K=888
          RTL DOM order (right→left reading): +50K, 200M+, 99%, 24/7
          Dividers: 3 vertical lines at 279/576/870 → between each stat
        -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-0">

          <!-- +50K مستقل نشط — color g-purple (fill_7R517F #1C0060) -->
          <div class="flex flex-col items-center py-4 relative">
            <span class="font-semibold text-[42px] leading-[54.6px] text-g-purple">+50K</span>
            <span class="font-medium text-sm leading-[21px] text-g-body mt-1">مستقل نشط</span>
            <!-- Divider at end side -->
            <div class="absolute end-0 top-[12%] h-[76px] w-px bg-black/10 hidden lg:block"></div>
          </div>

          <!-- 200M+ تمت معالجتها سنوياً — color g-green (fill_VD8FKR #006C51) -->
          <div class="flex flex-col items-center py-4 relative">
            <span class="font-semibold text-[42px] leading-[54.6px] text-g-green">200M+</span>
            <span class="font-medium text-sm leading-[21px] text-g-body mt-1">تمت معالجتها سنوياً</span>
            <div class="absolute end-0 top-[12%] h-[76px] w-px bg-black/10 hidden lg:block"></div>
          </div>

          <!-- 99% رضا العملاء — color g-purple -->
          <div class="flex flex-col items-center py-4 relative">
            <span class="font-semibold text-[42px] leading-[54.6px] text-g-purple">99%</span>
            <span class="font-medium text-sm leading-[21px] text-g-body mt-1">رضا العملاء</span>
            <div class="absolute end-0 top-[12%] h-[76px] w-px bg-black/10 hidden lg:block"></div>
          </div>

          <!-- 24/7 دعم فني متميز — color g-green -->
          <div class="flex flex-col items-center py-4">
            <span class="font-semibold text-[42px] leading-[54.6px] text-g-green">24/7</span>
            <span class="font-medium text-sm leading-[21px] text-g-body mt-1">دعم فني متميز</span>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Pain Points
    ════════════════════════════════════════ -->
    <section class="bg-g-light2 py-24 px-10">
      <div class="max-w-[1200px] mx-auto flex flex-col gap-16">

        <!-- Section heading block: layout_1KEC8I column align-start gap-16 -->
        <div class="flex flex-col items-start gap-4 w-full">
          <!-- Heading 2: SemiBold 42px #1C0060 -->
          <h2 class="font-semibold text-[42px] leading-[54.6px] text-g-purple text-start">
            لا تدع التشتت يعيق نموك
          </h2>
          <!-- Accent bar: 96×4px bg #006C51 rounded-full (layout_78WZGM) -->
          <div class="w-24 h-1 bg-g-green rounded-full"></div>
        </div>

        <!--
          4 cards grid: layout_TOD9Q1 (mode none → absolute positions):
          LTR x: Card1=888, Card2=592, Card3=296, Card4=0
          RTL DOM order (right→left): Card1, Card2, Card3, Card4
          Each: layout_92VKOT column align-end gap-[11.2] p-32 bg white radius-16 shadow
        -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

          <!-- Card 1: تتبع الأرباح -->
          <div
            class="flex flex-col items-start gap-3 p-8 bg-white rounded-[16px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)]">
            <!-- Icon overlay: rgba(99,248,199,0.3) w-12 h-12 rounded-[8px] -->
            <div class="flex items-center justify-center w-12 h-12 rounded-[8px] bg-[rgba(99,248,199,0.3)]">
              <!-- Chart icon -->
              <svg class="w-6 h-6 text-g-green" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
              </svg>
            </div>
            <h3 class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full">تتبع الأرباح</h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              اعرف أين تذهب أموالك بدقة وبدون الحاجة لفتح ملفات الإكسل المعقدة.
            </p>
          </div>

          <!-- Card 2: توفير الوقت -->
          <div
            class="flex flex-col items-start gap-3 p-8 bg-white rounded-[16px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)]">
            <div class="flex items-center justify-center w-12 h-12 rounded-[8px] bg-[rgba(99,248,199,0.3)]">
              <svg class="w-5 h-[25px] text-g-green" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full">توفير الوقت</h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              أتمتة المهام المتكررة مثل الفواتير الشهرية والمتابعة مع العملاء المتأخرين.
            </p>
          </div>

          <!-- Card 3: تنظيم العملاء -->
          <div
            class="flex flex-col items-start gap-3 p-8 bg-white rounded-[16px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)]">
            <div class="flex items-center justify-center w-12 h-12 rounded-[8px] bg-[rgba(99,248,199,0.3)]">
              <svg class="w-[26px] h-[22px] text-g-green" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
              </svg>
            </div>
            <h3 class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full">تنظيم العملاء</h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              قاعدة بيانات مركزية لكل تفاعلاتك، عقودك، ومدفوعاتك مع كل عميل.
            </p>
          </div>

          <!-- Card 4: التدفق النقدي -->
          <div
            class="flex flex-col items-start gap-3 p-8 bg-white rounded-[16px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)]">
            <div class="flex items-center justify-center w-12 h-12 rounded-[8px] bg-[rgba(99,248,199,0.3)]">
              <svg class="w-5 h-[18px] text-g-green" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
              </svg>
            </div>
            <h3 class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full">التدفق النقدي</h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              توقعات دقيقة لسيولتك النقدية القادمة لتخطيط قراراتك المالية بثقة.
            </p>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Features
    ════════════════════════════════════════ -->
    <section id="features" class="py-24 px-10">
      <div class="max-w-[1200px] mx-auto flex flex-col gap-16">

        <!-- Section heading -->
        <!-- alignItems:flex-end in Figma LTR = physical RIGHT = items-start in RTL -->
        <div class="flex flex-col items-start gap-4 w-full">
          <h2 class="font-semibold text-[42px] leading-[54.6px] text-g-purple text-start">
            مميزات صممت لنجاحك
          </h2>
          <div class="w-24 h-1 bg-g-green rounded-full"></div>
        </div>

        <!--
          3×2 grid. Figma x-positions LTR:
          Row1: عروض الأسعار=0, إدارة المشاريع=394.67, إدارة علاقات العملاء=789.33
          Row2: تقارير تحليلية=0, تتبع المصاريف=394.67, الفواتير الذكية=789.33
          RTL CSS Grid col1=rightmost. DOM order for RTL (CRM rightmost → Quotes leftmost):
          Row1: [CRM, Projects, Quotes] | Row2: [Invoices, Expenses, Reports]
          Card alignItems:flex-end (Figma LTR=right) → items-start in RTL = physical RIGHT ✓
          strokeWeight 0px 4px 0px 0px = right:4px = border-s-4 (inline-start=right in RTL) ✓
        -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

          <!-- إدارة علاقات العملاء (CRM) — rightmost in RTL -->
          <div class="group flex flex-col items-start gap-[7px] p-7 bg-white
                      border border-g-border border-s-4 border-s-g-purple
                      shadow-[0px_1px_3px_0px_rgba(0,0,0,0.06)]
                      transition-all duration-200 ease-out
                      hover:-translate-y-1 hover:border-g-border hover:border-s-g-green
                      hover:shadow-[0px_16px_32px_-8px_rgba(28,0,96,0.12)]
                      cursor-pointer">
            <!-- Icon: 33×30px -->
            <svg class="w-[33px] h-[30px] text-g-green transition-transform duration-200 group-hover:scale-110"
              fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h3
              class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full pt-[9px] transition-colors duration-200 group-hover:text-g-green">
              إدارة علاقات العملاء (CRM)
            </h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              نظم بيانات عملائك وسجل تواصلك معهم في مكان واحد آمن.
            </p>
          </div>

          <!-- إدارة المشاريع — middle -->
          <div class="group flex flex-col items-start gap-[7px] p-7 bg-white
                      border border-g-border border-s-4 border-s-g-purple
                      shadow-[0px_1px_3px_0px_rgba(0,0,0,0.06)]
                      transition-all duration-200 ease-out
                      hover:-translate-y-1 hover:border-g-border hover:border-s-g-green
                      hover:shadow-[0px_16px_32px_-8px_rgba(28,0,96,0.12)]
                      cursor-pointer">
            <svg class="w-[30px] h-[30px] text-g-green transition-transform duration-200 group-hover:scale-110"
              fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
            </svg>
            <h3
              class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full pt-[9px] transition-colors duration-200 group-hover:text-g-green">
              إدارة المشاريع
            </h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              تتبع سير العمل في مشاريعك وحدد الجداول الزمنية لكل مرحلة.
            </p>
          </div>

          <!-- عروض الأسعار — leftmost in RTL -->
          <div class="group flex flex-col items-start gap-[7px] p-7 bg-white
                      border border-g-border border-s-4 border-s-g-purple
                      shadow-[0px_1px_3px_0px_rgba(0,0,0,0.06)]
                      transition-all duration-200 ease-out
                      hover:-translate-y-1 hover:border-g-border hover:border-s-g-green
                      hover:shadow-[0px_16px_32px_-8px_rgba(28,0,96,0.12)]
                      cursor-pointer">
            <svg class="w-[24px] h-[30px] text-g-green transition-transform duration-200 group-hover:scale-110"
              fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" />
            </svg>
            <h3
              class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full pt-[9px] transition-colors duration-200 group-hover:text-g-green">
              عروض الأسعار
            </h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              أنشئ عروض أسعار احترافية وحولها إلى فواتير بنقرة واحدة.
            </p>
          </div>

          <!-- الفواتير الذكية — rightmost row 2 -->
          <div class="group flex flex-col items-start gap-[7px] p-7 bg-white
                      border border-g-border border-s-4 border-s-g-purple
                      shadow-[0px_1px_3px_0px_rgba(0,0,0,0.06)]
                      transition-all duration-200 ease-out
                      hover:-translate-y-1 hover:border-g-border hover:border-s-g-green
                      hover:shadow-[0px_16px_32px_-8px_rgba(28,0,96,0.12)]
                      cursor-pointer">
            <svg class="w-[27px] h-[30px] text-g-green transition-transform duration-200 group-hover:scale-110"
              fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            <h3
              class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full pt-[9px] transition-colors duration-200 group-hover:text-g-green">
              الفواتير الذكية
            </h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              نظام فواتير متكامل يدعم ضريبة القيمة المضافة ورمز الاستجابة السريع.
            </p>
          </div>

          <!-- تتبع المصاريف — middle row 2 -->
          <div class="group flex flex-col items-start gap-[7px] p-7 bg-white
                      border border-g-border border-s-4 border-s-g-purple
                      shadow-[0px_1px_3px_0px_rgba(0,0,0,0.06)]
                      transition-all duration-200 ease-out
                      hover:-translate-y-1 hover:border-g-border hover:border-s-g-green
                      hover:shadow-[0px_16px_32px_-8px_rgba(28,0,96,0.12)]
                      cursor-pointer">
            <svg class="w-[33px] h-[24px] text-g-green transition-transform duration-200 group-hover:scale-110"
              fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18-3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3m18-3V6m0 3H3" />
            </svg>
            <h3
              class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full pt-[9px] transition-colors duration-200 group-hover:text-g-green">
              تتبع المصاريف
            </h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              صور إيصالاتك ووثق نفقاتك لضمان عدم ضياع أي خصم ضريبي.
            </p>
          </div>

          <!-- تقارير تحليلية — leftmost row 2 -->
          <div class="group flex flex-col items-start gap-[7px] p-7 bg-white
                      border border-g-border border-s-4 border-s-g-purple
                      shadow-[0px_1px_3px_0px_rgba(0,0,0,0.06)]
                      transition-all duration-200 ease-out
                      hover:-translate-y-1 hover:border-g-border hover:border-s-g-green
                      hover:shadow-[0px_16px_32px_-8px_rgba(28,0,96,0.12)]
                      cursor-pointer">
            <svg class="w-[27px] h-[27px] text-g-green transition-transform duration-200 group-hover:scale-110"
              fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
            </svg>
            <h3
              class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-start w-full pt-[9px] transition-colors duration-200 group-hover:text-g-green">
              تقارير تحليلية
            </h3>
            <p class="font-normal text-base leading-[27.2px] text-g-body text-start">
              تقارير دورية شاملة تلخص أداء عملك المالي والتشغيلي.
            </p>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: How It Works
    ════════════════════════════════════════ -->
    <section class="bg-g-light2 py-24 px-10">
      <div class="max-w-[1200px] mx-auto flex flex-col gap-20">

        <!-- Heading centered: SemiBold 42px -->
        <h2 class="font-semibold text-[42px] leading-[54.6px] text-g-purple text-center w-full">
          ابدأ في 3 خطوات بسيطة
        </h2>

        <!--
          3 steps: layout_USV1J3 row space-between
          Figma DOM: [step3, step2, step1] — RTL reversal: DOM [step1, step2, step3]
          → step1 rightmost (inline-start in RTL), step3 leftmost
          Dashed connector line behind circles (absolute)
        -->
        <div class="relative flex flex-col md:flex-row items-start justify-between gap-10 md:gap-0">

          <!-- Dashed connector line (desktop only): layout_LNUNN0 stroke #006C51 dashed -->
          <div
            class="absolute hidden md:block top-[48px] start-[147px] end-[147px] h-0 border-t-2 border-dashed border-g-green">
          </div>

          <!-- Step 1: أنشئ حسابك — circle bg #006C51 (green filled), number white -->
          <!-- layout_0TL1D7 column center gap-11 -->
          <div class="relative flex flex-col items-center gap-[11px] w-full md:w-auto z-10">
            <!-- Circle: 96×96 border-[4px] border-g-light rounded-full bg g-green shadow -->
            <div
              class="flex items-center justify-center w-24 h-24 rounded-full bg-g-green border-[4px] border-g-light shadow-[0px_8px_10px_-6px_rgba(0,0,0,0.1),0px_20px_25px_-5px_rgba(0,0,0,0.1)]">
              <!-- Number: Bold 700 42px white -->
              <span class="font-bold text-[42px] leading-[54.6px] text-white">1</span>
            </div>
            <!-- Step label: SemiBold 24px #1C0060 centered -->
            <h3 class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-center">أنشئ حسابك</h3>
            <!-- Step body: Regular 16px #6B7280 w-[320px] centered -->
            <p class="font-normal text-base leading-[27.2px] text-g-muted text-center max-w-[320px]">
              سجل خلال أقل من دقيقة مجاناً وابدأ بربط خدماتك.
            </p>
          </div>

          <!-- Step 2: وثق معاملاتك — circle bg white, number g-green -->
          <div class="relative flex flex-col items-center gap-[11px] w-full md:w-auto z-10">
            <div
              class="flex items-center justify-center w-24 h-24 rounded-full bg-white border-[4px] border-g-light shadow-[0px_8px_10px_-6px_rgba(0,0,0,0.1),0px_20px_25px_-5px_rgba(0,0,0,0.1)]">
              <span class="font-bold text-[42px] leading-[54.6px] text-g-green">2</span>
            </div>
            <h3 class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-center">وثق معاملاتك</h3>
            <p class="font-normal text-base leading-[27.2px] text-g-muted text-center max-w-[320px]">
              أصدر أول فاتورة أو سجل مصروفاً جديداً بكل سهولة.
            </p>
          </div>

          <!-- Step 3: شاهد نموك — circle bg white, number g-green -->
          <div class="relative flex flex-col items-center gap-[11px] w-full md:w-auto z-10">
            <div
              class="flex items-center justify-center w-24 h-24 rounded-full bg-white border-[4px] border-g-light shadow-[0px_8px_10px_-6px_rgba(0,0,0,0.1),0px_20px_25px_-5px_rgba(0,0,0,0.1)]">
              <span class="font-bold text-[42px] leading-[54.6px] text-g-green">3</span>
            </div>
            <h3 class="font-semibold text-[24px] leading-[33.6px] text-g-purple text-center">شاهد نموك</h3>
            <p class="font-normal text-base leading-[27.2px] text-g-muted text-center max-w-[320px]">
              استلم تقارير مفصلة تساعدك على اتخاذ قرارات مالية أفضل.
            </p>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Testimonials
    ════════════════════════════════════════ -->
    <section class="bg-g-light py-24 px-10">
      <div class="max-w-[1200px] mx-auto flex flex-col gap-16">

        <!-- Section heading -->
        <div class="flex flex-col items-start gap-4 w-full">
          <h2 class="font-semibold text-[42px] leading-[54.6px] text-g-purple text-start">
            ماذا يقول شركاؤنا
          </h2>
          <div class="w-24 h-1 bg-g-green rounded-full"></div>
        </div>

        <!--
          3 cards in row: layout_TOD9Q1 (absolute positions in Figma)
          x-positions LTR: Card1=0, Card2=390.67, Card3=781.33
          RTL DOM order: Card3, Card2, Card1 → Card3(right), Card2(mid), Card1(left)
          But since Card3 is a different testimonial (عمر), and Cards 1&2 same (سارة duplicate in Figma)
          I'll use: Card1=سارة (right, green border), Card2=عمر (middle, purple border), Card3=third person (left, green border)
          Each card: layout_S89V3F column gap-24 p-[31px_32px_32px] bg #F8F9FD
          border-s-[8px]: strokeWeight "0px 8px 0px 0px" = right:8px = RTL inline-start
        -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- Card 1: سارة أحمد — border-s-8 border-s-g-green -->
          <div
            class="flex flex-col gap-6 p-[31px_32px_32px] bg-g-light2 border-s-[8px] border-s-g-green rounded-[8px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)]">
            <!-- Quote text: layout_C4FGV5 Regular 18px/30.6px #191C1F w:290 -->
            <p class="font-normal text-[18px] leading-[30.6px] text-g-dark text-start">
              "غيرت دراهم طريقتي في إدارة أعمالي تماماً. كنت أقضي أياماً في الحسابات، الآن كل شيء يتم بنقرة واحدة. الدعم
              الفني باللغة العربية مذهل."
            </p>
            <!-- Footer: layout_6OG44P row justify-end gap-16 -->
            <div class="flex items-center justify-start gap-4">
              <!-- Avatar: 48×48 rounded-full border-2 white shadow -->
              <div
                class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-[0px_2px_4px_-2px_rgba(0,0,0,0.1),0px_4px_6px_-1px_rgba(0,0,0,0.1)] flex-shrink-0">
                <img src="{{ asset('marketing/imgs/') }}testimonial-sarah-513477.png" alt="سارة أحمد"
                  class="w-full h-full object-cover" />
              </div>
              <!-- Name & role block: layout_07VM3R column align-start gap-0 -->
              <div class="flex flex-col items-start">
                <span class="font-bold text-base leading-6 text-g-purple">سارة أحمد</span>
                <span class="font-medium text-[13px] leading-[18.2px] text-g-body">مصممة مستقلة</span>
              </div>
            </div>
          </div>

          <!-- Card 2: عمر خالد — border-s-8 border-s-g-purple -->
          <div
            class="flex flex-col gap-6 p-[31px_32px_32px] bg-g-light2 border-s-[8px] border-s-g-purple rounded-[8px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)]">
            <p class="font-normal text-[18px] leading-[30.6px] text-g-dark text-start">
              "كصاحب شركة ناشئة، الوضوح المالي هو كل شيء. توفر لي دراهم تقارير دقيقة ساعدتنا في الحصول على جولة
              استثمارية بفضل دقة بياناتنا."
            </p>
            <div class="flex items-center justify-start gap-4">
              <div
                class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-[0px_2px_4px_-2px_rgba(0,0,0,0.1),0px_4px_6px_-1px_rgba(0,0,0,0.1)] flex-shrink-0">
                <img src="{{ asset('marketing/imgs/') }}testimonial-omar-513477.png" alt="عمر خالد"
                  class="w-full h-full object-cover" />
              </div>
              <div class="flex flex-col items-start">
                <span class="font-bold text-base leading-6 text-g-purple">عمر خالد</span>
                <span class="font-medium text-[13px] leading-[18.2px] text-g-body">مؤسس TechFlow</span>
              </div>
            </div>
          </div>

          <!-- Card 3: نورة السالم — border-s-8 border-s-g-green -->
          <div
            class="flex flex-col gap-6 p-[31px_32px_32px] bg-g-light2 border-s-[8px] border-s-g-green rounded-[8px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)]">
            <p class="font-normal text-[18px] leading-[30.6px] text-g-dark text-start">
              "وفّرت دراهم عليّ ساعات كل أسبوع. الفواتير تصدر بثوانٍ والتقارير تعطيني نظرة واضحة على عملي. أنصح به كل
              مستقل في السوق العربي."
            </p>
            <div class="flex items-center justify-start gap-4">
              <!-- Avatar placeholder (no Figma asset for 3rd) -->
              <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-g-green-lt to-g-green border-2 border-white shadow-[0px_2px_4px_-2px_rgba(0,0,0,0.1),0px_4px_6px_-1px_rgba(0,0,0,0.1)] flex-shrink-0 flex items-center justify-center">
                <span class="font-bold text-white text-base">ن</span>
              </div>
              <div class="flex flex-col items-start">
                <span class="font-bold text-base leading-6 text-g-purple">نورة السالم</span>
                <span class="font-medium text-[13px] leading-[18.2px] text-g-body">كاتبة محتوى</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Pricing Preview
    ════════════════════════════════════════ -->
    <section id="pricing" class="bg-g-light2 py-20 px-10">
      <div class="max-w-[1200px] mx-auto flex flex-col gap-16">

        <!-- Heading block: layout_2DXYAH gap-8 -->
        <div class="flex flex-col items-center gap-2 w-full">
          <h2 class="font-semibold text-[42px] leading-[54.6px] text-g-purple text-center w-full">
            أسعار تلائم طموحك
          </h2>
          <p class="font-normal text-base leading-[27.2px] text-g-body text-center">
            ابدأ مجاناً وقم بالترقية مع نمو عملك
          </p>

          <!-- Billing toggle: شهري first = right in RTL -->
          <div
            class="mt-7 inline-flex items-center gap-1 rounded-full bg-[linear-gradient(67deg,#310e8e_0%,#13c597_95%)] p-1 backdrop-blur-sm">
            <button type="button" data-billing="monthly"
              class="pricing-billing-toggle rounded-full bg-white px-6 py-2 text-sm font-semibold text-g-purple shadow transition-colors">شهري</button>
            <button type="button" data-billing="yearly"
              class="pricing-billing-toggle flex items-center gap-2 rounded-full px-5 py-2 text-sm font-medium text-white/80 transition-colors">
              سنوي
              <span
                class="pricing-save-badge rounded-full bg-g-green-lt/25 px-2.5 py-0.5 text-[11px] font-bold text-g-green-lt">وفّر حتى 25%</span>
            </button>
          </div>
        </div>

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
                <span>حتى 3 مشاريع</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>حتى 5 عملاء</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>5 فواتير شهرياً</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>3 عروض أسعار شهرياً</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>50 معاملة شهرياً</span>
              </li>
            </ul>
            @auth
            <a href="{{ route('dashboard') }}"
              class="mt-8 block rounded-lg border border-g-border px-4 py-3 text-center text-sm font-bold text-g-purple transition-colors hover:border-g-purple hover:bg-g-light2">انتقل للوحة التحكم</a>
            @else
            <a href="{{ route('register') }}"
              class="mt-8 block rounded-lg border border-g-border px-4 py-3 text-center text-sm font-bold text-g-purple transition-colors hover:border-g-purple hover:bg-g-light2">ابدأ مجاناً</a>
            @endauth
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
                <span data-plan="pro" class="pricing-price text-[44px] font-bold leading-none text-g-green">{{ config('billing.plans.pro.monthly.price', '17') }}</span>
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
                <span>عملاء غير محدودين</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>1,000 معاملة شهرياً</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>إرسال الفواتير بالبريد</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>الصناديق المالية</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>التقارير المتقدمة</span>
              </li>
            </ul>
            @auth
            <a href="{{ route('billing.upgrade') }}"
              class="mt-8 block rounded-lg bg-g-green px-4 py-3.5 text-center text-sm font-bold text-white transition-opacity hover:opacity-90">اشترك الآن</a>
            @else
            <a href="{{ route('register', ['plan' => 'pro', 'cycle' => 'monthly']) }}"
              class="mt-8 block rounded-lg bg-g-green px-4 py-3.5 text-center text-sm font-bold text-white transition-opacity hover:opacity-90">ابدأ الآن</a>
            @endauth
          </article>

          <!-- Card: الأعمال (Business) -->
          <article
            class="flex flex-col rounded-lg border-2 border-g-purple/40 bg-white p-8 transition-shadow hover:shadow-md">
            <div class="border-b border-g-purple/20 pb-5">
              <span
                class="inline-flex rounded-full bg-g-purple/10 px-3 py-1 text-xs font-semibold text-g-purple">{{ config('billing.plans.business.label', 'الأعمال') }}</span>
              <div class="mt-4 flex items-end gap-2">
                <span data-plan="business" class="pricing-price text-[44px] font-bold leading-none text-g-purple">{{ config('billing.plans.business.monthly.price', '45') }}</span>
                <span class="mb-1 text-sm text-g-muted">$ / شهرياً</span>
              </div>
            </div>
            <ul class="mt-6 flex flex-col gap-3.5 text-base text-g-dark">
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>كل مزايا الإحترافية</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>أعضاء فريق أكثر</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>API Access</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>صلاحيات متقدمة</span>
              </li>
              <li class="flex items-center gap-3">
                <span
                  class="shrink-0 grid size-5 place-items-center rounded-full bg-g-green text-[10px] font-bold text-white">✓</span>
                <span>دعم أولوية</span>
              </li>
            </ul>
            @auth
            <a href="{{ route('billing.upgrade') }}"
              class="mt-8 block rounded-lg border-2 border-g-purple px-4 py-3 text-center text-sm font-bold text-g-purple transition-colors hover:bg-g-light2">اشترك الآن</a>
            @else
            <a href="{{ route('register', ['plan' => 'business', 'cycle' => 'monthly']) }}"
              class="mt-8 block rounded-lg border-2 border-g-purple px-4 py-3 text-center text-sm font-bold text-g-purple transition-colors hover:bg-g-light2">ابدأ الآن</a>
            @endauth
          </article>

        </div>

        <!-- Note: currency + billing cycle -->
        <p class="text-center text-xs text-g-muted mt-2">
          الأسعار بالدولار الأمريكي، والدفع السنوي يُحسب كقيمة سنوية كاملة.
        </p>

      </div>
    </section>

    <!-- Section: FAQ -->
    <section id="faq" class="bg-white px-5 py-20">
      <div class="mx-auto max-w-[1000px]">
        <h2 class="mb-12 text-center text-[32px] font-bold text-g-navy sm:text-[40px]">الأسئلة الشائعة</h2>
        <div class="flex flex-col gap-4 md:flex-row md:items-start">

          <!-- Column 1 -->
          <div class="flex flex-1 flex-col gap-4">

            <article
              class="pricing-faq-item rounded-2xl border-2 border-g-navy bg-white p-6 transition-all duration-200 hover:shadow-md"
              data-faq-open="true">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">هل يمكنني استخدام دراهم مجاناً للأبد؟</span>
                <span
                  class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-navy/10 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <svg class="pricing-faq-icon-plus hidden size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                </span>
              </button>
              <div class="pricing-faq-answer mt-4 text-sm leading-7 text-g-body">
                نعم، الخطة المجانية دائمة وتغطي احتياجات المستقلين في بداية طريقهم. يمكنك الترقية فقط عندما تحتاج
                لمميزات إضافية.
              </div>
            </article>

            <article
              class="pricing-faq-item rounded-2xl border border-g-border bg-white p-6 transition-all duration-200 hover:shadow-md"
              data-faq-open="false">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">كيف يمكنني التواصل مع الدعم الفني؟</span>
                <span
                  class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-light2 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus hidden size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <svg class="pricing-faq-icon-plus size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                </span>
              </button>
              <div class="pricing-faq-answer hidden mt-4 text-sm leading-7 text-g-body">
                يمكنك التواصل معنا عبر البريد الإلكتروني أو من خلال قنوات الدعم داخل التطبيق حسب خطتك.
              </div>
            </article>

          </div>

          <!-- Column 2 -->
          <div class="flex flex-1 flex-col gap-4">

            <article
              class="pricing-faq-item rounded-2xl border border-g-border bg-white p-6 transition-all duration-200 hover:shadow-md"
              data-faq-open="false">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">ماذا يحدث لبياناتي إذا ألغيت اشتراكي؟</span>
                <span
                  class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-light2 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus hidden size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <svg class="pricing-faq-icon-plus size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                </span>
              </button>
              <div class="pricing-faq-answer hidden mt-4 text-sm leading-7 text-g-body">
                تبقى بياناتك قابلة للتصدير، ويمكنك تحميل الفواتير والعملاء والتقارير قبل إغلاق الاشتراك المدفوع.
              </div>
            </article>

            <article
              class="pricing-faq-item rounded-2xl border border-g-border bg-white p-6 transition-all duration-200 hover:shadow-md"
              data-faq-open="false">
              <button type="button" class="pricing-faq-trigger flex w-full items-center justify-between gap-4">
                <span class="text-start text-base font-bold text-g-navy">هل البرنامج متوافق مع الفاتورة
                  الإلكترونية؟</span>
                <span
                  class="pricing-faq-icon shrink-0 flex size-8 items-center justify-center rounded-full bg-g-light2 transition-all duration-200">
                  <svg class="pricing-faq-icon-minus hidden size-[14px] text-g-navy" viewBox="0 0 14 2" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <svg class="pricing-faq-icon-plus size-[14px] text-g-muted" viewBox="0 0 14 14" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  </svg>
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
        <span
          class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-[#fcb87d]">الفرصة
          بين يديك</span>
        <h2 class="text-[36px] font-bold leading-tight text-white sm:text-[56px] sm:leading-[70px]">
          جاهز لتنظيم
          <span class="block text-g-orange">حياتك المالية؟</span>
        </h2>
        <p class="max-w-[672px] pt-2 text-base leading-8 text-white/70 sm:text-lg">انضم إلى آلاف المستقلين الذين يديرون
          أعمالهم بثقة وسهولة. ابدأ اليوم مجاناً لمدة 14 يوماً.</p>
        <div class="mt-8 flex flex-col gap-4 sm:flex-row">
          <a href="#"
            class="rounded bg-g-orange px-10 py-5 text-lg font-bold text-white hover:opacity-90 transition-opacity">ابدأ
            الآن</a>
          <a href="#"
            class="rounded border-2 border-white/20 px-10 py-5 text-lg font-bold text-white hover:bg-white/10 transition-colors">احجز
            جلسة استشارية</a>
        </div>
      </div>
    </section>

  </main>


  <!-- Section: Footer -->
@endsection

@section('scripts')
<script src="{{ asset('marketing/js/home.js') }}"></script>
@endsection
