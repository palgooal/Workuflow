<x-guest-layout>

    {{-- العنوان --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">إنشاء حساب جديد</h1>
        <p class="mt-1 text-gray-500 text-sm">ابدأ مجاناً — لا يلزم بطاقة ائتمان</p>
    </div>

    {{-- خطأ نموذج التسجيل (توكن منتهي/مرفوض) — لا يجوز أن يُعاد تحميل الفورم بصمت --}}
    @error('_form_token')
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $message }}
        </div>
    @enderror

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Honeypot: مخفي عن البشر، يُملأ بواسطة البوتات --}}
        <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;width:0;height:0;overflow:hidden;" tabindex="-1">
            <label for="hp_website">Website (leave blank)</label>
            <input type="text" id="hp_website" name="website" tabindex="-1" autocomplete="off" value="">
        </div>

        {{-- توقيت الفورم: يكتشف الإرسال الآلي الفوري --}}
        <input type="hidden" name="_form_token" value="{{ $formToken }}">

        {{-- Plan Intent: نية الخطة المختارة من صفحة التسعير (اختياري) --}}
        {{-- يُملأ تلقائياً عند القدوم من CTA مثل /register?plan=pro&cycle=annual --}}
        <input type="hidden" name="plan_intent"  value="{{ old('plan_intent',  request('plan')) }}">
        <input type="hidden" name="cycle_intent" value="{{ old('cycle_intent', request('cycle', 'monthly')) }}">

        {{-- الاسم --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
            <input
                id="name" name="name" type="text"
                value="{{ old('name') }}"
                required autofocus autocomplete="name"
                placeholder="محمد العلي"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent
                       transition @error('name') border-red-400 @enderror"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- البريد الإلكتروني --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
            <input
                id="email" name="email" type="email"
                value="{{ old('email') }}"
                required autocomplete="email"
                placeholder="name@example.com"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent
                       transition @error('email') border-red-400 @enderror"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- رقم الهاتف --}}
        {{--
            Alpine component:
            - dialCode: رمز الدولة المختار (e.g. +970)
            - localNum: الرقم المحلي كما يكتبه المستخدم
            - phone (getter): E.164 = dialCode + localNum بعد تنظيفه
              التنظيف: حذف المسافات والشرطات والحروف + حذف الصفر الأول إن وُجد
            - الحقل المخفي name="phone" يستقبل القيمة المدمجة ← هو المُرسل والمُتحقق منه
            - phone_code و phone_local يُرسلان أيضاً لاستعادة old() عند فشل التحقق
        --}}
        <div
            x-data="{
                dialCode: '{{ old('phone_code', '+966') }}',
                localNum: '{{ old('phone_local', '') }}',
                get phone() {
                    let local = this.localNum.replace(/[\s\-]/g, '').replace(/\D/g, '');
                    if (local.startsWith('0')) local = local.slice(1);
                    return local.length ? this.dialCode + local : '';
                }
            }"
        >
            <label for="phone_local" class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
            <div class="flex gap-2">

                {{-- الرقم المحلي --}}
                {{-- تظهر بصرياً على يمين رمز الدولة (ترتيب DOM هذا مقصود لعرض RTL:
                     رمز الدولة يسار / رقم الجوال يمين) --}}
                <input
                    type="tel"
                    id="phone_local"
                    name="phone_local"
                    x-model="localNum"
                    required
                    autocomplete="tel-national"
                    placeholder="599123456"
                    inputmode="numeric"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent
                           transition @error('phone') border-red-400 @enderror"
                >

                {{-- رمز الدولة --}}
                <label for="phone_code" class="sr-only">رمز الدولة</label>
                <select
                    id="phone_code"
                    name="phone_code"
                    x-model="dialCode"
                    class="px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent
                           transition @error('phone') border-red-400 @enderror"
                >
                    <option value="+966">🇸🇦 +966</option>
                    <option value="+970">🇵🇸 +970</option>
                    <option value="+962">🇯🇴 +962</option>
                    <option value="+971">🇦🇪 +971</option>
                    <option value="+965">🇰🇼 +965</option>
                    <option value="+974">🇶🇦 +974</option>
                    <option value="+973">🇧🇭 +973</option>
                    <option value="+968">🇴🇲 +968</option>
                    <option value="+20">🇪🇬  +20</option>
                    <option value="+961">🇱🇧 +961</option>
                    <option value="+963">🇸🇾 +963</option>
                    <option value="+964">🇮🇶 +964</option>
                    <option value="+967">🇾🇪 +967</option>
                    <option value="+212">🇲🇦 +212</option>
                    <option value="+216">🇹🇳 +216</option>
                    <option value="+213">🇩🇿 +213</option>
                    <option value="+44">🇬🇧  +44</option>
                    <option value="+1">🇺🇸  +1</option>
                </select>

                {{-- الحقل المخفي: يحمل الرقم الكامل بصيغة E.164 → هو المُرسل للـ server --}}
                <input type="hidden" name="phone" :value="phone">

            </div>

            @error('phone')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- العملة الافتراضية --}}
        {{-- ملاحظة: حقل المنطقة الزمنية أُزيل من هنا (2026-07-08) — كان بدون أي
             تأثير فعلي بالكود، ويمكن ضبطه لاحقاً من صفحة الإعدادات عند الحاجة. --}}
        <div>
            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">العملة الافتراضية</label>
            <select
                id="currency" name="currency"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900
                       focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent
                       transition @error('currency') border-red-400 @enderror"
            >
                @foreach($currencies as $code => $label)
                    <option value="{{ $code }}" {{ old('currency', 'SAR') === $code ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('currency')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- كلمة المرور --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
            <input
                id="password" name="password" type="password"
                required autocomplete="new-password"
                placeholder="8 أحرف على الأقل"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent
                       transition @error('password') border-red-400 @enderror"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- تأكيد كلمة المرور --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">تأكيد كلمة المرور</label>
            <input
                id="password_confirmation" name="password_confirmation" type="password"
                required autocomplete="new-password"
                placeholder="أعد كتابة كلمة المرور"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent
                       transition @error('password') border-red-400 @enderror"
            >
        </div>

        {{-- زر التسجيل --}}
        <button
            type="submit"
            class="w-full py-3 px-4 bg-brand hover:opacity-90 text-white font-semibold rounded-xl
                   transition focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2"
        >
            إنشاء الحساب مجاناً
        </button>

        {{-- رابط تسجيل الدخول --}}
        <p class="text-center text-sm text-gray-500">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="text-brand hover:opacity-75 font-medium">
                تسجيل الدخول
            </a>
        </p>

    </form>

</x-guest-layout>
