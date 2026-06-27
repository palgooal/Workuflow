<?php

namespace App\Http\Requests\Referral;

use App\Modules\Referral\Enums\PayoutMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::enum(PayoutMethod::class)],
            'notes'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'method.required' => 'طريقة الاستلام مطلوبة.',
            'method.enum'     => 'طريقة الاستلام غير صالحة.',
        ];
    }
}
