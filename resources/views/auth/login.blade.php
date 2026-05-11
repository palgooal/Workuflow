<x-guest-layout>

    {{-- العنوان --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">مرحباً بعودتك</h2>
        <p class="mt-1 text-gray-500 text-sm">سجّل دخولك لمتابعة وضعك المالي</p>
    </div>

    {{-- رسالة الحالة (إعادة تعيين كلمة المرور، إلخ) --}}
    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- البريد الإلكتروني --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
            <input
                id="email" name="email" type="email"
                value="{{ old('email') }}"
                required autofocus autocomplete="username"
                placeholder="name@example.com"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                       transition @error('email') border-red-400 @enderror"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- كلمة المرور --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-700">
                        نسيت كلمة المرور؟
                    </a>
                @endif
            </div>
            <input
                id="password" name="password" type="password"
                required autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                       transition @error('password') border-red-400 @enderror"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- تذكرني --}}
        <div class="flex items-center gap-2">
            <input
                id="remember_me" name="remember" type="checkbox"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            >
            <label for="remember_me" class="text-sm text-gray-600">تذكّرني</label>
        </div>

        {{-- زر الدخول --}}
        <button
            type="submit"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl
                   transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            تسجيل الدخول
        </button>

        {{-- رابط التسجيل --}}
        <p class="text-center text-sm text-gray-500">
            ليس لديك حساب؟
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                إنشاء حساب مجاني
            </a>
        </p>

    </form>

</x-guest-layout>
