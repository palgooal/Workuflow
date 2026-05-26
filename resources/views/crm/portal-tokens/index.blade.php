<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    رموز بوابة العميل
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $client->name }}
                    @if($client->company) · {{ $client->company }} @endif
                </p>
            </div>
            <a href="{{ route('clients.show', $client->public_id) }}"
               class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                ملف العميل
            </a>
        </div>
    </x-slot>

    {{-- Toast --}}
    <div
        x-data="{ show: false, message: '', type: 'success' }"
        @show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 4500)"
        x-show="show"
        x-transition.opacity
        class="fixed top-5 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium pointer-events-none"
        :class="type === 'success' ? 'bg-emerald-600' : 'bg-red-600'"
        style="display:none"
    >
        <span x-text="message"></span>
    </div>

    <div class="py-8" x-data="portalTokenManager()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Security Banner --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3 text-sm text-amber-800">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-semibold">تحذير أمني — اقرأ قبل إنشاء رمز</p>
                    <p class="text-xs mt-1 text-amber-700">
                        الرمز يُعرض <strong>مرة واحدة فقط</strong> عند الإنشاء ثم لا يمكن استرجاعه.
                        نسخه وأرسله للعميل فوراً. الرمز المخزَّن في قاعدة البيانات هو hash مشفر فقط.
                    </p>
                </div>
            </div>

            {{-- New Token Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إنشاء رمز وصول جديد
                </h3>

                <div class="space-y-4">
                    {{-- Permissions --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الصلاحيات</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($permissions as $perm)
                                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-indigo-300 hover:bg-indigo-50 transition"
                                       :class="newToken.permissions.includes('{{ $perm->value }}') ? 'border-indigo-400 bg-indigo-50' : ''">
                                    <input type="checkbox"
                                           value="{{ $perm->value }}"
                                           @change="togglePermission('{{ $perm->value }}')"
                                           :checked="newToken.permissions.includes('{{ $perm->value }}')"
                                           class="mt-0.5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">{{ $perm->label() }}</div>
                                        <div class="text-xs text-gray-400">{{ $perm->description() }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- TTL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">مدة الصلاحية</label>
                        <div class="flex items-center gap-3">
                            <select x-model="newToken.ttl_days"
                                    class="text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="7">7 أيام</option>
                                <option value="14">14 يوماً</option>
                                <option value="30" selected>30 يوماً (افتراضي)</option>
                                <option value="60">60 يوماً</option>
                                <option value="90">90 يوماً</option>
                                <option value="180">6 أشهر</option>
                                <option value="365">سنة كاملة</option>
                            </select>
                            <span class="text-xs text-gray-400">
                                ينتهي بتاريخ
                                <span x-text="getExpiryDate()"></span>
                            </span>
                        </div>
                    </div>

                    <button @click="createToken()"
                            :disabled="newToken.permissions.length === 0 || creating"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="!creating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <svg x-show="creating" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8"/>
                        </svg>
                        <span x-text="creating ? 'جاري الإنشاء...' : 'إنشاء رمز الوصول'"></span>
                    </button>
                </div>
            </div>

            {{-- New Token Display (one-time) --}}
            <div x-show="createdToken" x-transition class="bg-emerald-50 border-2 border-emerald-400 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-emerald-800 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        تم إنشاء الرمز — انسخه الآن
                    </h3>
                    <span class="text-xs text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">يظهر مرة واحدة فقط</span>
                </div>

                {{-- Token Display --}}
                <div class="bg-white border border-emerald-300 rounded-xl p-3 mb-3">
                    <p class="text-xs text-gray-500 mb-1">رمز الوصول:</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs font-mono text-gray-800 flex-1 break-all leading-relaxed" x-text="createdToken?.plaintext"></code>
                        <button @click="copyToken(createdToken?.plaintext)"
                                class="shrink-0 p-2 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100 rounded-lg transition"
                                title="نسخ الرمز">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Portal URL --}}
                <div class="bg-white border border-emerald-300 rounded-xl p-3 mb-3">
                    <p class="text-xs text-gray-500 mb-1">رابط البوابة مع الرمز:</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs font-mono text-indigo-700 flex-1 break-all" x-text="createdToken?.url"></code>
                        <button @click="copyToken(createdToken?.url)"
                                class="shrink-0 p-2 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100 rounded-lg transition"
                                title="نسخ الرابط">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button @click="createdToken = null"
                        class="text-xs text-emerald-700 hover:text-emerald-900 underline">
                    لقد نسخته — أخفِ الرمز
                </button>
            </div>

            {{-- Active Tokens List --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">الرموز النشطة</h3>
                    <span class="text-xs text-gray-400">{{ $tokens->count() }} رمز</span>
                </div>

                @if($tokens->isEmpty())
                    <div class="py-10 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <p class="text-sm">لا توجد رموز نشطة</p>
                        <p class="text-xs mt-1">أنشئ رمزاً جديداً أعلاه</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($tokens as $token)
                            @php $expired = $token->isExpired(); @endphp
                            <div class="flex items-center gap-4 px-5 py-4 {{ $expired ? 'opacity-60' : '' }}">

                                {{-- Status dot --}}
                                <div class="w-2 h-2 rounded-full shrink-0 {{ $expired ? 'bg-gray-300' : 'bg-emerald-500' }}"></div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @foreach($token->permissions ?? [] as $perm)
                                            @php $permEnum = \App\Modules\CRM\Enums\PortalPermission::tryFrom($perm); @endphp
                                            @if($permEnum)
                                                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                                    {{ $permEnum->label() }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                                        <span>
                                            {{ $expired ? 'انتهى' : 'ينتهي' }}:
                                            {{ $token->expires_at->format('Y/m/d') }}
                                        </span>
                                        @if($token->last_used_at)
                                            <span>· آخر استخدام: {{ $token->last_used_at->diffForHumans() }}</span>
                                            @if($token->last_used_ip)
                                                <span>من {{ $token->last_used_ip }}</span>
                                            @endif
                                        @else
                                            <span>· لم يُستخدم بعد</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Revoke --}}
                                <button @click="revokeToken({{ $token->id }})"
                                        class="shrink-0 text-xs px-3 py-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-200 hover:border-red-300 rounded-lg transition">
                                    إبطال
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
    function portalTokenManager() {
        return {
            creating: false,
            createdToken: null,
            newToken: {
                permissions: ['view_invoices'],  // default
                ttl_days: 30,
            },

            togglePermission(perm) {
                const idx = this.newToken.permissions.indexOf(perm);
                if (idx >= 0) {
                    this.newToken.permissions.splice(idx, 1);
                } else {
                    this.newToken.permissions.push(perm);
                }
            },

            getExpiryDate() {
                const d = new Date();
                d.setDate(d.getDate() + parseInt(this.newToken.ttl_days));
                return d.toLocaleDateString('ar-SA', { year: 'numeric', month: 'long', day: 'numeric' });
            },

            async createToken() {
                if (this.newToken.permissions.length === 0) return;
                this.creating = true;

                try {
                    const res = await fetch('{{ route('clients.portal-tokens.store', $client->public_id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newToken),
                    });

                    const json = await res.json();

                    if (res.ok) {
                        this.createdToken = {
                            plaintext: json.plaintext_token,
                            url:       json.portal_url,
                        };
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: 'تم إنشاء الرمز — انسخه الآن', type: 'success' }
                        }));
                        // Reload token list after a short delay
                        setTimeout(() => window.location.reload(), 5000);
                    } else {
                        throw new Error(json.message || 'فشل الإنشاء');
                    }
                } catch (err) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: err.message, type: 'error' }
                    }));
                } finally {
                    this.creating = false;
                }
            },

            async revokeToken(tokenId) {
                if (!confirm('هل أنت متأكد من إبطال هذا الرمز؟ لن يتمكن العميل من الدخول به بعد الآن.')) return;

                try {
                    const res = await fetch(
                        `{{ route('clients.portal-tokens.destroy', ['client' => $client->public_id, 'token' => '__ID__']) }}`.replace('__ID__', tokenId),
                        {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                        }
                    );

                    const json = await res.json();
                    if (res.ok) {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: json.message, type: 'success' }
                        }));
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        throw new Error(json.message || 'فشل الإبطال');
                    }
                } catch (err) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: err.message, type: 'error' }
                    }));
                }
            },

            async copyToken(text) {
                if (!text) return;
                try {
                    await navigator.clipboard.writeText(text);
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'تم النسخ إلى الحافظة ✓', type: 'success' }
                    }));
                } catch {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'تعذر النسخ التلقائي — انسخه يدوياً', type: 'error' }
                    }));
                }
            },
        };
    }
    </script>

</x-app-layout>
