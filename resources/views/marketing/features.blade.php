@extends('layouts.marketing')

@section('title', 'دراهم — المميزات')

@section('content')
<main>

    <!-- ════════════════════════════════════════
         Section: Hero
    ════════════════════════════════════════ -->
    <section class="relative bg-g-light pt-20 overflow-hidden">

      <!-- Decorative blurs -->
      <div class="absolute bg-g-green blur-[40px] opacity-15 rounded-full pointer-events-none w-[500px] h-[500px]"
        style="top:-157px;inset-inline-start:-112px;"></div>
      <div class="absolute bg-g-purple blur-[40px] opacity-15 rounded-full pointer-events-none w-[400px] h-[400px]"
        style="bottom:-42px;inset-inline-end:-87px;"></div>

      <div class="relative z-10 max-w-[1200px] mx-auto px-6 pt-24 pb-18 flex flex-col items-center gap-6 text-center">

        <!-- Heading -->
        <h1 class="font-bold text-[40px] md:text-[52px] lg:text-[63px] leading-[1.3] text-center">
          <span class="block text-g-dark">كل الأدوات التي يحتاجها المستقل</span>
          <span class="block bg-gradient-to-r from-g-green to-g-purple bg-clip-text text-transparent">في منصة
            واحدة</span>
        </h1>

        <!-- Subtitle -->
        <p class="text-g-body text-lg leading-[1.7] max-w-3xl">
          أدر مشاريعك، فواتيرك، وعلاقاتك مع العملاء في مكان واحد مصمم خصيصاً للسوق العربي.
        </p>
      </div>

      <!-- ── Anchor Badges Strip ─────────────────────────────── -->
      <div class="relative z-10 max-w-[1200px] mx-auto px-6 pb-16">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

          <!-- CRM -->
          <a href="#feature-crm"
            class="bg-white border-s-4 border-s-g-green rounded-[14px] ps-7 pe-6 py-6 flex flex-col items-center gap-2 shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)] hover:shadow-md transition-shadow">
            <svg class="w-6 h-5 text-g-green" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="font-bold text-base text-g-purple text-center">نظام CRM</span>
          </a>

          <!-- Projects -->
          <a href="#feature-projects"
            class="bg-white border-s-4 border-s-g-green rounded-[14px] ps-7 pe-6 py-6 flex flex-col items-center gap-2 shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)] hover:shadow-md transition-shadow">
            <svg class="w-5 h-[18px] text-g-green" fill="none" stroke="currentColor" stroke-width="1.7"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
            </svg>
            <span class="font-bold text-base text-g-purple text-center">المشاريع</span>
          </a>

          <!-- Quotes -->
          <a href="#feature-quotes"
            class="bg-white border-s-4 border-s-g-green rounded-[14px] ps-7 pe-6 py-6 flex flex-col items-center gap-2 shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)] hover:shadow-md transition-shadow">
            <svg class="w-4 h-5 text-g-green" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" />
            </svg>
            <span class="font-bold text-base text-g-purple text-center">عروض الأسعار</span>
          </a>

          <!-- Invoices -->
          <a href="#feature-invoices"
            class="bg-white border-s-4 border-s-g-green rounded-[14px] ps-7 pe-6 py-6 flex flex-col items-center gap-2 shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)] hover:shadow-md transition-shadow">
            <svg class="w-[18px] h-5 text-g-green" fill="none" stroke="currentColor" stroke-width="1.7"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            <span class="font-bold text-base text-g-purple text-center">الفواتير</span>
          </a>

          <!-- Expenses -->
          <a href="#feature-expenses"
            class="bg-white border-s-4 border-s-g-green rounded-[14px] ps-7 pe-6 py-6 flex flex-col items-center gap-2 shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)] hover:shadow-md transition-shadow">
            <svg class="w-[19px] h-[18px] text-g-green" fill="none" stroke="currentColor" stroke-width="1.7"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            <span class="font-bold text-base text-g-purple text-center">المصاريف</span>
          </a>

          <!-- Analytics -->
          <a href="#feature-analytics"
            class="bg-white border-s-4 border-s-g-green rounded-[14px] ps-7 pe-6 py-6 flex flex-col items-center gap-2 shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)] hover:shadow-md transition-shadow">
            <svg class="w-[18px] h-[18px] text-g-green" fill="none" stroke="currentColor" stroke-width="1.7"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
            </svg>
            <span class="font-bold text-base text-g-purple text-center">التحليلات</span>
          </a>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Feature 1 — CRM
    ════════════════════════════════════════ -->
    <section id="feature-crm" class="bg-white py-24 px-5">
      <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

          <!-- Image (LEFT in RTL — second in DOM) -->
          <div class="rounded-[14px] overflow-hidden shadow-[0px_20px_30px_-10px_rgba(28,0,96,0.05)] aspect-video">
            <img src="https://www.figma.com/api/mcp/asset/3f3c5bd6-523f-4944-9045-cc2833d905fd"
              alt="لوحة تحكم نظام CRM العربي" class="w-full h-full object-cover" />
          </div>

          <!-- Text (RIGHT in RTL — first in DOM) -->
          <div class="flex flex-col gap-4 items-start">
            <!-- Badge -->
            <span class="bg-[rgba(99,248,199,0.2)] text-g-green text-base px-4 py-1 rounded-full">إدارة العملاء</span>
            <!-- Title -->
            <h2 class="text-[30px] font-semibold text-g-purple leading-[1.35] text-start w-full">
              نظام CRM عربي متكامل<br>ابنِ علاقات تدوم
            </h2>
            <!-- Body -->
            <p class="text-g-body text-base leading-[1.65] text-start pt-2">
              لا تضع وقتك في البحث بين رسائل البريد والإكسل. سجل بيانات عملائك، تاريخ تواصلهم، ومشاريعهم في مكان واحد
              منظم. الواجهة تدعم اللغة العربية بالكامل لتسهيل سير عملك اليومي.
            </p>
            <!-- Checklist -->
            <ul class="flex flex-col gap-4 w-full pt-4">
              <li class="flex items-center justify-start gap-3">
                <svg class="w-4 h-4" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M6.45 10.95L11.7375 5.6625L10.6875 4.6125L6.45 8.85L4.3125 6.7125L3.2625 7.7625L6.45 10.95ZM7.5 15C6.4625 15 5.4875 14.8031 4.575 14.4094C3.6625 14.0156 2.86875 13.4812 2.19375 12.8062C1.51875 12.1312 0.984375 11.3375 0.590625 10.425C0.196875 9.5125 0 8.5375 0 7.5C0 6.4625 0.196875 5.4875 0.590625 4.575C0.984375 3.6625 1.51875 2.86875 2.19375 2.19375C2.86875 1.51875 3.6625 0.984375 4.575 0.590625C5.4875 0.196875 6.4625 0 7.5 0C8.5375 0 9.5125 0.196875 10.425 0.590625C11.3375 0.984375 12.1312 1.51875 12.8062 2.19375C13.4812 2.86875 14.0156 3.6625 14.4094 4.575C14.8031 5.4875 15 6.4625 15 7.5C15 8.5375 14.8031 9.5125 14.4094 10.425C14.0156 11.3375 13.4812 12.1312 12.8062 12.8062C12.1312 13.4812 11.3375 14.0156 10.425 14.4094C9.5125 14.8031 8.5375 15 7.5 15ZM7.5 13.5C9.175 13.5 10.5938 12.9188 11.7563 11.7563C12.9188 10.5938 13.5 9.175 13.5 7.5C13.5 5.825 12.9188 4.40625 11.7563 3.24375C10.5938 2.08125 9.175 1.5 7.5 1.5C5.825 1.5 4.40625 2.08125 3.24375 3.24375C2.08125 4.40625 1.5 5.825 1.5 7.5C1.5 9.175 2.08125 10.5938 3.24375 11.7563C4.40625 12.9188 5.825 13.5 7.5 13.5Z"
                    fill="#006C51" />
                </svg>
                <span class="text-g-dark text-base text-start">قاعدة بيانات مركزية آمنة</span>
              </li>
              <li class="flex items-center justify-start gap-3">
                <svg class="w-4 h-4" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M6.45 10.95L11.7375 5.6625L10.6875 4.6125L6.45 8.85L4.3125 6.7125L3.2625 7.7625L6.45 10.95ZM7.5 15C6.4625 15 5.4875 14.8031 4.575 14.4094C3.6625 14.0156 2.86875 13.4812 2.19375 12.8062C1.51875 12.1312 0.984375 11.3375 0.590625 10.425C0.196875 9.5125 0 8.5375 0 7.5C0 6.4625 0.196875 5.4875 0.590625 4.575C0.984375 3.6625 1.51875 2.86875 2.19375 2.19375C2.86875 1.51875 3.6625 0.984375 4.575 0.590625C5.4875 0.196875 6.4625 0 7.5 0C8.5375 0 9.5125 0.196875 10.425 0.590625C11.3375 0.984375 12.1312 1.51875 12.8062 2.19375C13.4812 2.86875 14.0156 3.6625 14.4094 4.575C14.8031 5.4875 15 6.4625 15 7.5C15 8.5375 14.8031 9.5125 14.4094 10.425C14.0156 11.3375 13.4812 12.1312 12.8062 12.8062C12.1312 13.4812 11.3375 14.0156 10.425 14.4094C9.5125 14.8031 8.5375 15 7.5 15ZM7.5 13.5C9.175 13.5 10.5938 12.9188 11.7563 11.7563C12.9188 10.5938 13.5 9.175 13.5 7.5C13.5 5.825 12.9188 4.40625 11.7563 3.24375C10.5938 2.08125 9.175 1.5 7.5 1.5C5.825 1.5 4.40625 2.08125 3.24375 3.24375C2.08125 4.40625 1.5 5.825 1.5 7.5C1.5 9.175 2.08125 10.5938 3.24375 11.7563C4.40625 12.9188 5.825 13.5 7.5 13.5Z"
                    fill="#006C51" />
                </svg>
                <span class="text-g-dark text-base text-start">تتبع سجل التواصل والملاحظات</span>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Feature 2 — Projects
    ════════════════════════════════════════ -->
    <section id="feature-projects" class="bg-g-light2 py-24 px-5">
      <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

          <!-- Text (LEFT in RTL — second in DOM) -->
          <div class="flex flex-col gap-4 items-start">
            <span class="bg-[#e6deff] text-g-purple text-base px-4 py-1 rounded-full">إدارة المشاريع</span>
            <h2 class="text-[30px] font-semibold text-g-purple leading-[1.35] text-start w-full">
              تتبع مشاريعك وفريقك بسلاسة تامة
            </h2>
            <p class="text-g-body text-base leading-[1.65] text-start pt-2">
              نظم مهامك، راقب التقدم، وتأكد من تسليم مشاريعك في المواعيد المحددة. مع نظام دراهم، ستحصل على رؤية شاملة
              لكل مشروع من البداية وحتى التحصيل المالي.
            </p>
            <!-- Mini stats cards -->
            <div class="grid grid-cols-2 gap-4 w-full pt-4">
              <div class="bg-white rounded-lg p-4 shadow-sm flex flex-col gap-1 items-start">
                <span class="font-bold text-base text-g-purple text-start">نسبة الإنجاز</span>
                <span class="text-base text-g-green text-start">تحديث فوري للتقدم</span>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-sm flex flex-col gap-1 items-start">
                <span class="font-bold text-base text-g-purple text-start">الربحية</span>
                <span class="text-base text-g-green text-start">حساب دقيق للأرباح</span>
              </div>
            </div>
          </div>

          <!-- Image (RIGHT in RTL — first in DOM) -->
          <div class="rounded-[14px] overflow-hidden shadow-[0px_20px_30px_-10px_rgba(28,0,96,0.05)] aspect-video">
            <img src="https://www.figma.com/api/mcp/asset/4e874b99-4eb9-4329-9f05-f08eff4f3cec"
              alt="بطاقة إدارة المشاريع مع شريط التقدم" class="w-full object-cover" />
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Feature 3 — Quotes
    ════════════════════════════════════════ -->
    <section id="feature-quotes" class="bg-white py-24 px-5">
      <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

          <!-- Image — portrait mockup (LEFT in RTL — second in DOM) -->

          <!-- Image (RIGHT in RTL — first in DOM) -->
          <div class="rounded-[14px] overflow-hidden shadow-[0px_20px_30px_-10px_rgba(28,0,96,0.05)] aspect-video">
            <img src="https://www.figma.com/api/mcp/asset/75578573-00d0-416e-827a-dc05ee1c6491"
              alt="بطاقة إدارة المشاريع مع شريط التقدم" class="w-full object-cover" />
          </div>

          <!-- Text (RIGHT in RTL — first in DOM) -->
          <div class="flex flex-col gap-4 items-start">
            <span class="bg-[rgba(99,248,199,0.2)] text-g-green text-base px-4 py-1 rounded-full">المبيعات</span>
            <h2 class="text-[30px] font-semibold text-g-purple leading-[1.35] text-start w-full">
              عروض أسعار احترافية تفوز بالصفقات
            </h2>
            <p class="text-g-body text-base leading-[1.65] text-start pt-2">
              انطباع العميل الأول هو الأهم. صمم وأرسل عروض أسعار بتنسيق احترافي يعكس جودة عملك. يمكن للعملاء قبول العروض
              بضغطة زر واحدة لتتحول تلقائياً إلى مشاريع نشطة.
            </p>
            <!-- Action buttons -->
            <div class="flex items-center gap-4 pt-4">
              <a href="{{ route('register') }}"
                class="bg-g-purple text-white font-normal text-base px-8 py-[13px] rounded-lg hover:opacity-90 transition-opacity">
                جرب إنشاء عرض
              </a>
              <a href="#"
                class="border border-[#797584] text-g-purple font-normal text-base px-8 py-[13px] rounded-lg hover:bg-g-light transition-colors">
                شاهد النماذج
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Feature 4 — Invoices
    ════════════════════════════════════════ -->
    <section id="feature-invoices" class="bg-g-light2 py-24 px-5">
      <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

          <!-- Text (LEFT in RTL — second in DOM) -->
          <div class="flex flex-col gap-4 items-start">
            <span class="bg-[#e6deff] text-g-purple text-base px-4 py-1 rounded-full">الفوترة</span>
            <h2 class="text-g-purple leading-[1.35] text-start w-full">
              <span class="block text-[30px] font-semibold">فواتير عربية احترافية</span>
              <span class="block text-[20px] font-semibold">أصدرها وتابع تحصيلها</span>
            </h2>
            <p class="text-g-body text-base leading-[1.65] text-start pt-2">
              أصدر فواتيرك بثوانٍ معدودة وبما يتوافق مع متطلبات ضريبة القيمة المضافة. نظام متابعة التحصيل الآلي يرسل
              تذكيرات لعملائك لضمان وصول مستحقاتك في وقتها.
            </p>
            <!-- QR callout -->
            <div class="flex items-center gap-4 pt-4 w-full justify-start">
              <div class="bg-g-green rounded-full p-2.5 flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </div>
              <div class="flex flex-col items-start gap-1">
                <span class="font-bold text-base text-g-purple text-start">دعم QR Code</span>
                <span class="text-g-body text-base text-start">متوافق مع هيئة الزكاة والضريبة والجمارك.</span>
              </div>
            </div>
          </div>

          <div class="rounded-[14px] overflow-hidden shadow-[0px_20px_30px_-10px_rgba(28,0,96,0.05)] aspect-video">
            <img src="https://www.figma.com/api/mcp/asset/dd00b187-dcd0-4e35-bef7-9839cdb503d1"
              alt="بطاقة إدارة المشاريع مع شريط التقدم" class="w-full object-cover" />
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Feature 5 — Expenses
    ════════════════════════════════════════ -->
    <section id="feature-expenses" class="bg-white py-24 px-5">
      <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

          <div class="rounded-[14px] overflow-hidden shadow-[0px_20px_30px_-10px_rgba(28,0,96,0.05)] aspect-video">
            <img src="https://www.figma.com/api/mcp/asset/884075a5-e7e0-4ad2-8799-653c8ba22663"
              alt="بطاقة إدارة المشاريع مع شريط التقدم" class="w-full object-cover" />
          </div>

          <!-- Text (RIGHT in RTL — first in DOM) -->
          <div class="flex flex-col gap-4 items-start">
            <span class="bg-[rgba(99,248,199,0.2)] text-g-green text-base px-4 py-1 rounded-full">المالية</span>
            <h2 class="text-g-purple leading-[1.35] text-start w-full">
              <span class="block text-[30px] font-semibold">راقب تدفقك النقدي</span>
              <span class="block text-[20px] font-semibold">لا مفاجآت مالية</span>
            </h2>
            <p class="text-g-body text-base leading-[1.65] text-start pt-2 pb-4">
              تحكم في مصروفاتك كما تتحكم في أرباحك. سجل اشتراكاتك، مشترياتك، وفواتير الموردين لتصل إلى تحليل دقيق لصافي
              الربح الشهري والسنوي.
            </p>
            <!-- Quote block -->
            <blockquote class="bg-[#edeef2] border-s-4 border-s-g-green rounded-[14px] ps-7 pe-6 py-6 w-full">
              <p class="text-g-purple font-light text-base leading-[1.65] text-start">
                "منذ بدأت استخدام تتبع المصاريف، اكتشفت اشتراكات شهرية لم أكن أستخدمها، مما وفر لي مئات الريالات
                شهرياً."
              </p>
            </blockquote>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Feature 6 — Analytics
    ════════════════════════════════════════ -->
    <section id="feature-analytics" class="bg-g-light2 py-24 px-5">
      <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

          <!-- Text (LEFT in RTL — second in DOM) -->
          <div class="flex flex-col gap-4 items-start">
            <span class="bg-[#e6deff] text-g-purple text-base px-4 py-1 rounded-full">التقارير</span>
            <h2 class="text-[30px] font-semibold text-g-purple leading-[1.35] text-start w-full">
              اتخذ قراراتك بناءً على أرقام واضحة
            </h2>
            <p class="text-g-body text-base leading-[1.65] text-start pt-2">
              لا مزيد من التخمين. لوحة تحليلات دراهم تمنحك رؤية فورية لأداء عملك. من أين تأتي أرباحك؟ وأين تذهب مصاريفك؟
              كل الأجوبة في رسوم بيانية تفاعلية سهلة الفهم.
            </p>
            <!-- Stats -->
            <div class="flex items-center gap-6 pt-4">
              <div class="flex flex-col items-center gap-0.5">
                <span class="font-bold text-base text-g-purple">100+</span>
                <span class="text-g-body text-base">تقرير مخصص</span>
              </div>
              <div class="w-px h-10 bg-[#c9c4d5]"></div>
              <div class="flex flex-col items-center gap-0.5">
                <span class="font-bold text-base text-g-green">98%</span>
                <span class="text-g-body text-base">دقة البيانات</span>
              </div>
            </div>
          </div>

          <div class="rounded-[14px] overflow-hidden shadow-[0px_20px_30px_-10px_rgba(28,0,96,0.05)] aspect-video">
            <img src="https://www.figma.com/api/mcp/asset/5cf93c8a-78dd-4846-a7c2-32674c44c9dd"
              alt="بطاقة إدارة المشاريع مع شريط التقدم" class="w-full object-cover" />
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
@endsection

@section('scripts')
<script src="{{ asset('marketing/js/features.js') }}"></script>
@endsection
