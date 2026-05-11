<x-guest-layout>

    {{-- العنوان --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">إنشاء حساب جديد</h2>
        <p class="mt-1 text-gray-500 text-sm">ابدأ مجاناً — لا يلزم بطاقة ائتمان</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- الاسم --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
            <input
                id="name" name="name" type="text"
                value="{{ old('name') }}"
                required autofocus autocomplete="name"
                placeholder="محمد العلي"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                       transition @error('name') border-red-400 @enderror"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
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
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                       transition @error('email') border-red-400 @enderror"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- العملة والمنطقة الزمنية --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">العملة الافتراضية</label>
                <select
                    id="currency" name="currency"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                           transition @error('currency') border-red-400 @enderror"
                >
                    @foreach($currencies as $code => $label)
                        <option value="{{ $code }}" {{ old('currency', 'SAR') === $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('currency')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">المنطقة الزمنية</label>
                <select
                    id="timezone" name="timezone"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                           transition @error('timezone') border-red-400 @enderror"
                >
                    @foreach($timezones as $tz => $label)
                        <option value="{{ $tz }}" {{ old('timezone', 'Asia/Riyadh') === $tz ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('timezone')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- كلمة المرور --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
            <input
                id="password" name="password" type="password"
                required autocomplete="new-password"
                placeholder="8 أحرف على الأقل"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                       transition @error('password') border-red-400 @enderror"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
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
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
        </div>

        {{-- زر التسجيل --}}
        <button
            type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl
                   transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            إنشاء الحساب مجاناً
        </button>

        {{-- رابط تسجيل الدخول --}}
        <p class="text-center text-sm text-gray-500">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                تسجيل الدخول
            </a>
        </p>

    </form>

</x-guest-layout>
