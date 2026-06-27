<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class JoinAffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:affiliates,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'الاسم مطلوب.',
            'email.required'  => 'البريد الإلكتروني مطلوب.',
            'email.email'     => 'البريد الإلكتروني غير صحيح.',
            'email.unique'    => 'هذا البريد الإلكتروني مسجَّل بالفعل في برنامج الشركاء.',
        ];
    }
}
