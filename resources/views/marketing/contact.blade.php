@extends('layouts.marketing')

@section('title', 'دراهم — تواصل معنا')

@section('content')
<main>

    <!-- ════════════════════════════════════════
         Section: Hero
    ════════════════════════════════════════ -->
    <section class="bg-gradient-to-l from-[#310e8e] to-[#13c597] pt-20">
      <div class="max-w-[1200px] mx-auto px-10 py-16 flex flex-col items-center gap-5 text-center">

        <!-- Badge -->
        <div class="bg-white/20 backdrop-blur-sm px-5 py-1.5 rounded-full">
          <span class="text-white text-sm font-medium">تواصل معنا</span>
        </div>

        <!-- Heading -->
        <h1 class="text-[42px] font-semibold text-white leading-[1.3]">نحن هنا للمساعدة</h1>

        <!-- Subtitle -->
        <p class="text-white/90 text-base leading-[1.7] max-w-lg">
          سؤال، اقتراح، أو شراكة — فريقنا يرد خلال ٢٤ ساعة في أيام العمل
        </p>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Main Contact Block
    ════════════════════════════════════════ -->
    <section class="bg-g-light py-24 px-5">
      <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-[3fr_2fr] gap-8 items-start">

          <!-- ── Right Column: Contact Form (60%) ─────────────── -->
          <div class="bg-white rounded-[14px] px-8 pt-8 pb-12 shadow-[0px_20px_40px_-15px_rgba(13,13,31,0.12)]">

            <!-- Form header -->
            <div class="flex flex-col items-start gap-1 mb-6">
              <h2 class="text-g-purple text-2xl font-semibold text-start">أرسل رسالتك</h2>
              <p class="text-g-muted text-base text-start">وسنعود إليك في أقرب وقت ممكن</p>
            </div>

            <form id="contact-form" novalidate class="flex flex-col gap-6">

              <!-- Row: Name + Email -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- الاسم الكامل -->
                <div class="flex flex-col gap-2">
                  <label for="field-name" class="text-g-purple text-base text-start pe-1">الاسم الكامل</label>
                  <input id="field-name" type="text" placeholder="مثال: عبدالله محمد"
                    class="w-full h-12 border border-g-border rounded-lg px-4 text-g-dark text-base placeholder:text-g-muted text-start focus:outline-none focus:border-g-green transition-colors" />
                </div>

                <!-- البريد الإلكتروني -->
                <div class="flex flex-col gap-2">
                  <label for="field-email" class="text-g-purple text-base text-start pe-1">البريد الإلكتروني</label>
                  <input id="field-email" type="email" placeholder="name@example.com" dir="ltr"
                    class="w-full h-12 border border-g-border rounded-lg px-4 text-g-dark text-base placeholder:text-g-muted text-start focus:outline-none focus:border-g-green transition-colors" />
                </div>
              </div>

              <!-- الموضوع -->
              <div class="flex flex-col gap-2">
                <label for="field-subject" class="text-g-purple text-base text-start pe-1">الموضوع</label>
                <div class="relative">
                  <select id="field-subject"
                    class="w-full h-12 border border-g-border rounded-lg ps-4 pe-10 text-g-dark text-base appearance-none bg-white focus:outline-none focus:border-g-green transition-colors cursor-pointer text-start">
                    <option value="" disabled selected>اختر موضوع الرسالة</option>
                    <option value="support">دعم فني</option>
                    <option value="billing">الفواتير والاشتراكات</option>
                    <option value="partnership">شراكة تجارية</option>
                    <option value="feedback">اقتراح أو ملاحظة</option>
                    <option value="other">أخرى</option>
                  </select>
                  <!-- Arrow icon -->
                  <span class="absolute end-3 top-1/2 -translate-y-1/2 pointer-events-none text-g-muted">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                  </span>
                </div>
              </div>

              <!-- نوع حسابك -->
              <div class="flex flex-col gap-3">
                <label class="text-g-purple text-base text-start pe-1">نوع حسابك</label>
                <div class="flex gap-3 flex-wrap">
                  <button type="button" data-account="free"
                    class="account-btn flex-1 min-w-[120px] h-11 border border-g-green bg-g-green rounded-lg text-white text-base text-center transition-all active-account">
                    مستخدم مجاني
                  </button>
                  <button type="button" data-account="pro"
                    class="account-btn flex-1 min-w-[120px] h-11 border border-g-border rounded-lg text-g-muted text-base text-center transition-all hover:border-g-green">
                    مشترك احترافي
                  </button>
                  <button type="button" data-account="none"
                    class="account-btn flex-1 min-w-[120px] h-11 border border-g-border rounded-lg text-g-muted text-base text-center transition-all hover:border-g-green">
                    لست مستخدماً بعد
                  </button>
                </div>
              </div>

              <!-- رسالتك -->
              <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between px-1">
                  <label for="field-message" class="text-g-purple text-base text-start">رسالتك</label>
                  <span id="char-count" class="text-g-muted text-xs">1000 / 0
                  </span>
                </div>
                <textarea id="field-message" rows="6" maxlength="1000" placeholder="اكتب تفاصيل استفسارك هنا..."
                  class="w-full border border-g-border rounded-lg px-4 py-4 text-g-dark text-base placeholder:text-g-muted text-start resize-none focus:outline-none focus:border-g-green transition-colors"></textarea>
              </div>

              <!-- Newsletter checkbox -->
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" id="field-newsletter"
                  class="w-5 h-5 rounded border border-g-border accent-g-green shrink-0 cursor-pointer" />
                <span class="text-g-muted text-sm text-start leading-relaxed flex-1">
                  أوافق على استلام التحديثات والنشرات الإخبارية من دراهم (يمكنك إلغاء الاشتراك في أي وقت).
                </span>
              </label>

              <!-- Submit -->
              <button type="submit"
                class="w-full h-[52px] bg-g-green text-white font-medium text-base rounded-lg flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
                إرسال الرسالة
              </button>

              <!-- Success message (hidden) -->
              <div id="form-success"
                class="hidden bg-[#d1fae5] border border-g-green text-g-green rounded-lg px-4 py-3 text-center text-base font-medium">
                تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.
              </div>

            </form>
          </div>
          <!-- /Right Column (Form) -->

          <!-- ── Left Column: Info Cards (40%) ────────────────── -->
          <aside class="flex flex-col gap-4">

            <!-- Card: البريد الإلكتروني -->
            <div
              class="bg-white rounded-[14px] p-6 flex flex-col gap-3 items-start shadow-[0px_10px_30px_-10px_rgba(13,13,31,0.08)]">
              <div class="bg-[rgba(99,248,199,0.2)] w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-g-green" fill="none" stroke="currentColor" stroke-width="1.7"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
              </div>
              <div class="flex flex-col gap-2 w-full items-start">
                <h3 class="text-g-purple text-2xl font-bold text-start">البريد الإلكتروني</h3>
                <p class="text-g-purple font-semibold text-base text-start" dir="ltr">support@darahum.com</p>
                <p class="text-g-muted text-sm text-start">نرد خلال ٢٤ ساعة في أيام العمل</p>
              </div>
              <div class="w-full pt-2">
                <a href="mailto:support@darahum.com"
                  class="flex items-center justify-start gap-3 text-g-green text-base hover:opacity-75 transition-opacity">
                  أرسل بريداً إلكترونياً
                  <svg class="w-2.5 h-2.5 rtl:rotate-180" fill="currentColor" viewBox="0 0 10 10">
                    <path
                      d="M8.293 4.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L6.086 5 2.879 1.793A1 1 0 014.293.379l4 4z" />
                  </svg>
                </a>
              </div>
            </div>

            <!-- Card: واتساب -->
            <div
              class="bg-white rounded-[14px] p-6 flex flex-col gap-3 items-start shadow-[0px_10px_30px_-10px_rgba(13,13,31,0.08)]">
              <div class="bg-[rgba(99,248,199,0.2)] w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-g-green" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
              </div>
              <div class="flex flex-col gap-2 w-full items-start">
                <h3 class="text-g-purple text-2xl font-bold text-start">واتساب</h3>
                <p class="text-g-purple font-semibold text-base text-start">متاح لمستخدمي خطة الفريق</p>
                <p class="text-g-muted text-sm text-start">دعم أولوية عبر واتساب</p>
              </div>
              <div class="w-full pt-2">
                <a href="#"
                  class="flex items-center justify-start gap-3 text-g-green text-base hover:opacity-75 transition-opacity">
                  ابدأ محادثة
                  <svg class="w-2.5 h-2.5 rtl:rotate-180" fill="currentColor" viewBox="0 0 10 10">
                    <path
                      d="M8.293 4.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L6.086 5 2.879 1.793A1 1 0 014.293.379l4 4z" />
                  </svg>
                </a>
              </div>
            </div>

            <!-- Card: وقت الاستجابة -->
            <div
              class="bg-white rounded-[14px] p-6 flex flex-col gap-3 items-start shadow-[0px_10px_30px_-10px_rgba(13,13,31,0.08)]">
              <div class="bg-[rgba(99,248,199,0.2)] w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-g-green" fill="none" stroke="currentColor" stroke-width="1.7"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex flex-col gap-2 w-full items-start">
                <h3 class="text-g-purple text-2xl font-bold text-start">وقت الاستجابة</h3>
                <p class="text-g-purple font-semibold text-base text-start">أقل من ٢٤ ساعة</p>
                <p class="text-g-muted text-sm text-start">الأحد - الخميس · ٩ص - ٦م</p>
              </div>
            </div>

            <!-- Card: مركز المساعدة -->
            <div
              class="bg-white rounded-[14px] p-6 flex flex-col gap-3 items-start shadow-[0px_10px_30px_-10px_rgba(13,13,31,0.08)]">
              <div class="bg-[rgba(99,248,199,0.2)] w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-g-green" fill="none" stroke="currentColor" stroke-width="1.7"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
              </div>
              <div class="flex flex-col gap-2 w-full items-start">
                <h3 class="text-g-purple text-2xl font-bold text-start">مركز المساعدة</h3>
                <p class="text-g-purple font-semibold text-base text-start">أكثر من ٥٠ مقالة ودرس</p>
              </div>
              <div class="w-full pt-2">
                <a href="#"
                  class="flex items-center justify-start gap-3 text-g-green text-base hover:opacity-75 transition-opacity">
                  تصفح مركز المساعدة
                  <svg class="w-2.5 h-2.5 rtl:rotate-180" fill="currentColor" viewBox="0 0 10 10">
                    <path
                      d="M8.293 4.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L6.086 5 2.879 1.793A1 1 0 014.293.379l4 4z" />
                  </svg>
                </a>
              </div>
            </div>

            <!-- Social Links -->
            <div class="flex items-center justify-center gap-6 pt-2">
              <!-- Instagram -->
              <a href="#" class="text-g-muted hover:text-g-green transition-colors" aria-label="Instagram">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                </svg>
              </a>
              <!-- Twitter / X -->
              <a href="#" class="text-g-muted hover:text-g-green transition-colors" aria-label="Twitter">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                </svg>
              </a>
              <!-- LinkedIn -->
              <a href="#" class="text-g-muted hover:text-g-green transition-colors" aria-label="LinkedIn">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                </svg>
              </a>
            </div>

          </aside>
          <!-- /Left Column (Info Cards) -->

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: FAQ Quick Links
    ════════════════════════════════════════ -->
    <section class="relative bg-white py-24 px-5 overflow-hidden">

      <!-- Decorative bg glow — top-end (right in RTL, left in LTR) -->
      <div
        class="absolute -top-20 end-[-80px] w-[380px] h-[380px] rounded-full bg-g-green-lt blur-[120px] opacity-[0.07] pointer-events-none">
      </div>
      <!-- Decorative bg glow — bottom-start (left in RTL, right in LTR) -->
      <div
        class="absolute -bottom-20 start-[-80px] w-[300px] h-[300px] rounded-full bg-g-purple blur-[100px] opacity-[0.06] pointer-events-none">
      </div>

      <div class="relative z-10 max-w-[1200px] mx-auto px-6 flex flex-col gap-12">

        <!-- Heading -->
        <div class="flex flex-col items-center gap-3 text-center">
          <h2 class="text-[42px] font-semibold text-g-purple leading-[1.3]">ربما تجد إجابتك هنا</h2>
          <p class="text-g-muted text-base">قبل التواصل معنا، تحقق من الأسئلة الشائعة</p>
        </div>

        <!-- FAQ Cards: 3 columns -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- Card: أسئلة الأسعار -->
          <div
            class="group bg-g-light border-s-4 border-s-g-green-lt rounded-[16px] ps-8 pe-7 py-8 flex flex-col gap-5 shadow-[0px_8px_28px_-8px_rgba(13,13,31,0.07)] hover:shadow-[0px_20px_44px_-8px_rgba(13,13,31,0.13)] hover:-translate-y-1.5 transition-all duration-300">
            <!-- Icon container -->
            <div
              class="w-12 h-12 rounded-xl bg-g-green-lt/10 flex items-center justify-center text-g-green self-start shrink-0">
              <svg class="w-6 h-6"  viewBox="0 0 33 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M19.5 13.5C18.25 13.5 17.1875 13.0625 16.3125 12.1875C15.4375 11.3125 15 10.25 15 9C15 7.75 15.4375 6.6875 16.3125 5.8125C17.1875 4.9375 18.25 4.5 19.5 4.5C20.75 4.5 21.8125 4.9375 22.6875 5.8125C23.5625 6.6875 24 7.75 24 9C24 10.25 23.5625 11.3125 22.6875 12.1875C21.8125 13.0625 20.75 13.5 19.5 13.5ZM9 18C8.175 18 7.46875 17.7062 6.88125 17.1187C6.29375 16.5312 6 15.825 6 15V3C6 2.175 6.29375 1.46875 6.88125 0.88125C7.46875 0.29375 8.175 0 9 0H30C30.825 0 31.5313 0.29375 32.1188 0.88125C32.7063 1.46875 33 2.175 33 3V15C33 15.825 32.7063 16.5312 32.1188 17.1187C31.5313 17.7062 30.825 18 30 18H9ZM12 15H27C27 14.175 27.2938 13.4688 27.8813 12.8813C28.4688 12.2938 29.175 12 30 12V6C29.175 6 28.4688 5.70625 27.8813 5.11875C27.2938 4.53125 27 3.825 27 3H12C12 3.825 11.7062 4.53125 11.1187 5.11875C10.5312 5.70625 9.825 6 9 6V12C9.825 12 10.5312 12.2938 11.1187 12.8813C11.7062 13.4688 12 14.175 12 15ZM28.5 24H3C2.175 24 1.46875 23.7062 0.88125 23.1187C0.29375 22.5312 0 21.825 0 21V4.5H3V21H28.5V24ZM9 15V3V15Z"
                  fill="#13C597" />
              </svg>
            </div>
            <h3 class="text-g-purple text-xl font-semibold text-start">أسئلة الأسعار</h3>
            <p class="text-g-muted text-sm text-start leading-relaxed flex-1">
              كل ما يتعلق بالخطط المدفوعة، طرق الدفع المتاحة، وكيفية الترقية أو الإلغاء.
            </p>
            <a href="{{ route('marketing.pricing') }}"
              class="inline-flex items-center gap-2 text-g-purple-mid font-semibold text-sm group-hover:gap-3 transition-all duration-200">
              تصفح الأسئلة
              <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </a>
          </div>

          <!-- Card: دليل البدء السريع -->
          <div
            class="group bg-g-light border-s-4 border-s-g-green-lt rounded-[16px] ps-8 pe-7 py-8 flex flex-col gap-5 shadow-[0px_8px_28px_-8px_rgba(13,13,31,0.07)] hover:shadow-[0px_20px_44px_-8px_rgba(13,13,31,0.13)] hover:-translate-y-1.5 transition-all duration-300">
            <!-- Icon container -->
            <div
              class="w-12 h-12 rounded-xl bg-g-green-lt/10 flex items-center justify-center text-g-green self-start shrink-0">
              <svg class="w-6 h-6" viewBox="0 0 33 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M16.5 29.25C15.3 28.3 14 27.5625 12.6 27.0375C11.2 26.5125 9.75 26.25 8.25 26.25C7.2 26.25 6.16875 26.3875 5.15625 26.6625C4.14375 26.9375 3.175 27.325 2.25 27.825C1.725 28.1 1.21875 28.0875 0.73125 27.7875C0.24375 27.4875 0 27.05 0 26.475V8.4C0 8.125 0.06875 7.8625 0.20625 7.6125C0.34375 7.3625 0.55 7.175 0.825 7.05C1.975 6.45 3.175 6 4.425 5.7C5.675 5.4 6.95 5.25 8.25 5.25C9.7 5.25 11.1188 5.4375 12.5063 5.8125C13.8938 6.1875 15.225 6.75 16.5 7.5V25.65C17.775 24.85 19.1125 24.25 20.5125 23.85C21.9125 23.45 23.325 23.25 24.75 23.25C25.65 23.25 26.5312 23.325 27.3937 23.475C28.2562 23.625 29.125 23.85 30 24.15V6.15C30.375 6.275 30.7438 6.40625 31.1063 6.54375C31.4688 6.68125 31.825 6.85 32.175 7.05C32.45 7.175 32.6562 7.3625 32.7938 7.6125C32.9313 7.8625 33 8.125 33 8.4V26.475C33 27.05 32.7562 27.4875 32.2687 27.7875C31.7812 28.0875 31.275 28.1 30.75 27.825C29.825 27.325 28.8563 26.9375 27.8438 26.6625C26.8312 26.3875 25.8 26.25 24.75 26.25C23.25 26.25 21.8 26.5125 20.4 27.0375C19 27.5625 17.7 28.3 16.5 29.25ZM19.5 21.75V7.5L27 0V15L19.5 21.75ZM13.5 24.1875V9.3375C12.675 8.9875 11.8187 8.71875 10.9312 8.53125C10.0437 8.34375 9.15 8.25 8.25 8.25C7.325 8.25 6.425 8.3375 5.55 8.5125C4.675 8.6875 3.825 8.95 3 9.3V24.1875C3.875 23.8625 4.74375 23.625 5.60625 23.475C6.46875 23.325 7.35 23.25 8.25 23.25C9.15 23.25 10.0312 23.325 10.8938 23.475C11.7563 23.625 12.625 23.8625 13.5 24.1875ZM13.5 24.1875V9.3375V24.1875Z"
                  fill="#13C597" />
              </svg>
            </div>
            <h3 class="text-g-purple text-xl font-semibold text-start">دليل البدء السريع</h3>
            <p class="text-g-muted text-sm text-start leading-relaxed flex-1">
              خطوات بسيطة لربط حساباتك البنكية وبدء تتبع مصاريفك باحترافية منذ اليوم الأول.
            </p>
            <a href="#"
              class="inline-flex items-center gap-2 text-g-purple-mid font-semibold text-sm group-hover:gap-3 transition-all duration-200">
              ابدأ التعلم
              <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </a>
          </div>

          <!-- Card: الأمان والخصوصية -->
          <div
            class="group bg-g-light border-s-4 border-s-g-green-lt rounded-[16px] ps-8 pe-7 py-8 flex flex-col gap-5 shadow-[0px_8px_28px_-8px_rgba(13,13,31,0.07)] hover:shadow-[0px_20px_44px_-8px_rgba(13,13,31,0.13)] hover:-translate-y-1.5 transition-all duration-300">
            <!-- Icon container -->
            <div
              class="w-12 h-12 rounded-xl bg-g-green-lt/10 flex items-center justify-center text-g-green self-start shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
              </svg>
            </div>
            <h3 class="text-g-purple text-xl font-semibold text-start">الأمان والخصوصية</h3>
            <p class="text-g-muted text-sm text-start leading-relaxed flex-1">
              تعرف على كيفية حمايتنا لبياناتك المالية وبروتوكولات التشفير التي نستخدمها.
            </p>
            <a href="#"
              class="inline-flex items-center gap-2 text-g-purple-mid font-semibold text-sm group-hover:gap-3 transition-all duration-200">
              اعرف المزيد
              <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </a>
          </div>

        </div>
      </div>
    </section>


    <!-- ════════════════════════════════════════
         Section: Final CTA
    ════════════════════════════════════════ -->
    <section class="relative py-24 px-5 overflow-hidden bg-gradient-to-l from-[#13c597] to-[#310e8e]">
      <!-- Decorative glow -->
      <div
        class="absolute rounded-[12px] pointer-events-none blur-[60px] bg-[rgba(28,0,96,0.1)] w-[600px] h-[600px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
      </div>

      <!-- Decorative circles — physical left/right intentional: visual position must match gradient regardless of dir -->
      <div
        class="absolute top-[-192px] -right-28 w-[384px] h-96 rounded-full border-40 border-white/10 pointer-events-none">
      </div>
      <div
        class="absolute bottom-[-160px] -left-16 w-[256px] h-64 rounded-full border-20 border-white/10 pointer-events-none">
      </div>

      <div class="relative z-10 max-w-[1200px] mx-auto px-6 flex flex-col items-center gap-4 text-center">
        <h2 class="text-5xl font-bold text-white leading-tight">لم تجد ما تبحث عنه؟</h2>
        <p class="text-white text-base leading-relaxed max-w-xl mt-4">
          ابدأ بتجربة دراهم مجاناً وتواصل معنا من داخل المنصة للحصول على دعم أسرع.
        </p>
        <div class="mt-12">
          <a href="{{ route('register') }}"
            class="bg-white text-g-purple-mid font-bold text-lg px-10 py-4 rounded-xl shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)] hover:opacity-90 transition-opacity">
            ابدأ تجربتك المجانية
          </a>
        </div>
      </div>
    </section>

  </main>
@endsection

@section('scripts')
<script src="{{ asset('marketing/js/contact.js') }}"></script>
@endsection
