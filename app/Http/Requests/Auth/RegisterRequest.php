<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'confirmed', Password::defaults()],
            'currency'  => ['required', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'timezone'  => ['required', 'string', 'timezone:all'],
        ];
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
            'currency.required' => 'العملة مطلوبة',
            'currency.in'       => 'العملة المختارة غير مدعومة',
            'timezone.required' => 'المنطقة الزمنية مطلوبة',
            'timezone.timezone' => 'المنطقة الزمنية غير صحيحة',
        ];
    }
}
