<?php

namespace App\Http\Requests\Settings;

use App\Support\Helpers\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'currency'          => ['required', 'string', Rule::in(Currency::codes())],
            'timezone'          => ['required', 'string', 'timezone'],
            'target_margin_pct' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency.required' => 'العملة مطلوبة.',
            'currency.in'       => 'العملة المحددة غير مدعومة.',
            'timezone.required' => 'المنطقة الزمنية مطلوبة.',
            'timezone.timezone' => 'المنطقة الزمنية غير صالحة.',
        ];
    }
}
