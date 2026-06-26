<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'currency'  => ['required', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'timezone'  => ['required', 'string', 'timezone:all'],
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
            // التوكن مفقود أو مزيّف أو التشفير فاشل — رفض صامت
            $validator->errors()->add('_form_token', 'invalid');
            return;
        }

        // الإرسال خلال أقل من ثانيتين = ربما بوت
        if ((now()->timestamp - $renderedAt) < 2) {
            $validator->errors()->add('_form_token', 'too_fast');
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
            'timezone.required' => 'المنطقة الزمنية مطلوبة',
            'timezone.timezone' => 'المنطقة الزمنية غير صحيحة',
        ];
    }
}
