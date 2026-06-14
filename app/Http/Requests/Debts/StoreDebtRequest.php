<?php

namespace App\Http\Requests\Debts;

use App\Support\Enums\DebtType;
use App\Support\Helpers\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreDebtRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'       => ['required', new Enum(DebtType::class)],
            'party_name' => ['required', 'string', 'max:100'],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'currency'   => ['required', 'string', Rule::in(Currency::codes())],
            'due_date'   => ['nullable', 'date', 'after_or_equal:today'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'       => 'نوع الدين مطلوب.',
            'party_name.required' => 'اسم الطرف الآخر مطلوب.',
            'amount.required'     => 'المبلغ مطلوب.',
            'amount.min'          => 'المبلغ يجب أن يكون أكبر من صفر.',
            'currency.required'   => 'العملة مطلوبة.',
            'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون اليوم أو بعده.',
        ];
    }
}
