<?php

namespace App\Http\Requests\Transactions;

use App\Support\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateTransactionRequest extends FormRequest
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
            'currency'         => ['required', 'string', 'in:SAR,ILS,USD,EUR,GBP,AED,KWD'],
            'description'      => ['required', 'string', 'max:255'],
            'payee'            => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'project_id'       => ['nullable', 'string', 'exists:projects,id'],
            'wallet_id'        => ['required', 'string', Rule::exists('wallets', 'id')->where('user_id', auth()->id())],
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
            'description.required'      => 'وصف المعاملة مطلوب.',
            'transaction_date.required' => 'تاريخ المعاملة مطلوب.',
            'transaction_date.date'     => 'تاريخ المعاملة غير صالح.',
            'wallet_id.required'        => 'يجب تحديد الصندوق الذي ستذهب إليه الأموال.',
            'wallet_id.exists'          => 'الصندوق المحدد غير موجود.',
        ];
    }
}
