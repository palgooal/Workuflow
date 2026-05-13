<?php

namespace App\Http\Requests\Recurring;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', 'in:income,expense'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
            'frequency'   => ['required', 'in:daily,weekly,monthly,yearly'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['nullable', 'date', 'after:start_date'],
            'category_id' => ['nullable', 'ulid', 'exists:categories,id'],
            'project_id'  => ['nullable', 'ulid', 'exists:projects,id'],
            'currency'    => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'        => 'نوع المعاملة مطلوب.',
            'amount.required'      => 'المبلغ مطلوب.',
            'amount.min'           => 'المبلغ يجب أن يكون أكبر من صفر.',
            'description.required' => 'الوصف مطلوب.',
            'frequency.required'   => 'التكرار مطلوب.',
            'start_date.required'  => 'تاريخ البداية مطلوب.',
            'end_date.after'       => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية.',
        ];
    }
}
