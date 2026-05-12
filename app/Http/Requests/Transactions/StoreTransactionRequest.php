<?php

namespace App\Http\Requests\Transactions;

use App\Support\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => ['required', new Enum(TransactionType::class)],
            'amount'           => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'currency'         => ['required', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'description'      => ['required', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'project_id'       => ['nullable', 'string', 'exists:projects,id'],
            'category_id'      => ['nullable', 'string', 'exists:categories,id'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'reference'        => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'             => 'نوع المعاملة مطلوب.',
            'amount.required'           => 'المبلغ مطلوب.',
            'amount.numeric'            => 'المبلغ يجب أن يكون رقماً.',
            'amount.min'                => 'المبلغ يجب أن يكون أكبر من صفر.',
            'currency.required'         => 'العملة مطلوبة.',
            'currency.in'               => 'العملة غير مدعومة.',
            'description.required'      => 'وصف المعاملة مطلوب.',
            'description.max'           => 'الوصف يجب ألا يتجاوز 255 حرفاً.',
            'transaction_date.required' => 'تاريخ المعاملة مطلوب.',
            'transaction_date.date'     => 'تاريخ المعاملة غير صالح.',
            'project_id.exists'         => 'المشروع المحدد غير موجود.',
            'category_id.exists'        => 'الفئة المحددة غير موجودة.',
        ];
    }
}
