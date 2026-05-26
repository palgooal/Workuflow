<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\CRM\Models\ClientFollowUp::class);
    }

    public function rules(): array
    {
        return [
            'client_id'   => [
                'required',
                'integer',
                Rule::exists('clients', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],

            'title'       => ['required', 'string', 'max:200'],
            'notes'       => ['nullable', 'string', 'max:2000'],

            'due_at'      => ['required', 'date', 'after:now'],

            'priority'    => ['nullable', 'integer', 'min:1', 'max:5'],

            'reminder_at' => [
                'nullable',
                'date',
                // التذكير يجب أن يكون قبل موعد المتابعة
                'before:due_at',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'  => 'يجب تحديد العميل.',
            'client_id.exists'    => 'العميل المحدد غير موجود أو لا تملكه.',
            'title.required'      => 'عنوان المتابعة مطلوب.',
            'title.max'           => 'العنوان لا يتجاوز 200 حرف.',
            'due_at.required'     => 'تاريخ الاستحقاق مطلوب.',
            'due_at.after'        => 'تاريخ الاستحقاق يجب أن يكون في المستقبل.',
            'priority.min'        => 'الأولوية بين 1 و 5.',
            'priority.max'        => 'الأولوية بين 1 و 5.',
            'reminder_at.before'  => 'التذكير يجب أن يكون قبل موعد المتابعة.',
        ];
    }
}
