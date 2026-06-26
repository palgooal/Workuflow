<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{5,14}$/',
                         Rule::unique('users', 'phone')->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'الاسم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'البريد الإلكتروني غير صالح.',
            'email.unique'   => 'البريد الإلكتروني مستخدم بالفعل.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex'    => 'صيغة رقم الهاتف غير صحيحة. مثال: +970599123456',
            'phone.unique'   => 'رقم الهاتف هذا مستخدم من قِبل حساب آخر.',
            'phone.max'      => 'رقم الهاتف طويل جداً.',
        ];
    }
}
