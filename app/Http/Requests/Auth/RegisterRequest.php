<?php

namespace App\Http\Requests\Auth;

use App\Support\Helpers\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Fallback بدون JavaScript: الحقل المُرسَل فعلياً (phone) يُملأ عادة عبر Alpine.js
     * من phone_code + phone_local (انظر resources/views/auth/register.blade.php).
     * إذا تعطّل JS، يصل phone فارغاً بينما phone_code/phone_local يصلان بشكل طبيعي
     * (حقول <select>/<input> عادية لا تعتمد على JS لإرسال قيمتها).
     * هذا التابع يعيد بناء نفس صيغة E.164 يدوياً في هذه الحالة فقط، دون أي تغيير
     * على طريقة التخزين أو قواعد التحقق أو سلوك Alpine عندما يعمل JS بشكل طبيعي.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            return; // Alpine.js ملأ الحقل بنجاح — لا حاجة لأي تدخل
        }

        if (! $this->filled('phone_code') || ! $this->filled('phone_local')) {
            return; // لا بيانات كافية لإعادة البناء — يُترك الأمر لقاعدة "required" الحالية
        }

        $local = preg_replace('/\D/', '', (string) $this->input('phone_local'));

        if ($local !== '' && $local[0] === '0') {
            $local = substr($local, 1);
        }

        if ($local !== '') {
            $this->merge([
                'phone' => $this->input('phone_code') . $local,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // ── Anti-spam ────────────────────────────────────────────────
            'website'      => ['prohibited'],        // Honeypot: يجب أن يبقى فارغاً
            '_form_token'  => ['required', 'string'], // Timing token: مطلوب دائماً
            // ── Registration fields ───────────────────────────────────────
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $atPos = strrpos($value, '@');
                    if ($atPos === false) return; // حقل البريد غير صالح — تُعالجه قاعدة email
                    $domain = strtolower(trim(substr($value, $atPos + 1)));
                    if (in_array($domain, config('blocked-email-domains', []))) {
                        $fail('لا يمكن استخدام بريد إلكتروني مؤقت للتسجيل.');
                    }
                },
            ],
            'phone'     => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{5,14}$/', 'unique:users,phone'],
            'password'  => ['required', 'confirmed', Password::defaults()],
            'currency'  => ['required', 'string', Rule::in(Currency::codes())],
            // ملاحظة: حقل timezone أُزيل من فورم التسجيل (2026-07-08) — القيمة
            // تُضبط تلقائياً في RegisterUserAction، ولا تعود تُطلب أو تُتحقَّق هنا.

            // ── Plan Intent (اختياري — يأتي من CTAs صفحة التسعير) ────────
            // لا يؤثر على التحقق أو الاشتراك — يُخزَّن في الـ session فقط.
            'plan_intent'  => ['nullable', 'string', 'in:pro,business'],
            'cycle_intent' => ['nullable', 'string', 'in:monthly,annual'],
        ];
    }

    /**
     * فحص توقيت الفورم بعد اجتياز القواعد الأساسية.
     * إذا كان هناك أي خطأ مسبق (Honeypot أو حقول ناقصة)، يُتجاوز الفحص.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return; // الطلب فاشل بالفعل — لا داعي لمزيد من الفحص
            }

            $this->validateFormTiming($validator);
        });
    }

    private function validateFormTiming(Validator $validator): void
    {
        $token = $this->input('_form_token', '');

        try {
            $renderedAt = (int) decrypt($token);
        } catch (\Throwable) {
            // التوكن مفقود أو مزيّف أو التشفير فاشل — يُعرض للمستخدم عبر @error('_form_token')
            $validator->errors()->add('_form_token', 'انتهت صلاحية نموذج التسجيل، يرجى إعادة تحميل الصفحة والمحاولة مرة أخرى.');
            return;
        }

        // الإرسال خلال أقل من ثانيتين = ربما بوت — يُعرض للمستخدم عبر @error('_form_token')
        if ((now()->timestamp - $renderedAt) < 2) {
            $validator->errors()->add('_form_token', 'لا يمكن إرسال النموذج حالياً، يرجى المحاولة مرة أخرى.');
        }
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'الاسم مطلوب',
            'email.required'    => 'البريد الإلكتروني مطلوب',
            'email.unique'      => 'هذا البريد الإلكتروني مسجّل مسبقاً',
            'email.email'       => 'صيغة البريد الإلكتروني غير صحيحة',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed'=> 'كلمتا المرور غير متطابقتين',
            'phone.required'    => 'رقم الهاتف مطلوب.',
            'phone.regex'       => 'صيغة رقم الهاتف غير صحيحة. مثال: +970599123456',
            'phone.unique'      => 'رقم الهاتف هذا مسجّل بالفعل.',
            'phone.max'         => 'رقم الهاتف طويل جداً.',
            'currency.required' => 'العملة مطلوبة',
            'currency.in'       => 'العملة المختارة غير مدعومة',
        ];
    }
}
