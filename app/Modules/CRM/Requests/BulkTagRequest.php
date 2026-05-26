<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        // المصادقة الأساسية — ملكية كل عميل تُتحقق منها داخل الـ Action
        // can('create') كافٍ لأنه يمثّل "مستخدم نشط مع خطة صالحة"
        return $this->user()->can('create', \App\Models\Client::class);
    }

    public function rules(): array
    {
        return [
            'client_ids'   => ['required', 'array', 'min:1', 'max:500'],
            'client_ids.*' => [
                'integer',
                // التحقق من أن العميل ينتمي للمستخدم الحالي
                Rule::exists('clients', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],

            'tag_ids'   => ['required', 'array', 'min:1', 'max:20'],
            'tag_ids.*' => ['integer', 'exists:client_tags,id'],

            'action' => ['required', Rule::in(['assign', 'remove'])],
        ];
    }

    public function messages(): array
    {
        return [
            'client_ids.required'  => 'يجب اختيار عميل واحد على الأقل.',
            'client_ids.min'       => 'يجب اختيار عميل واحد على الأقل.',
            'client_ids.max'       => 'لا يمكن تحديد أكثر من 500 عميل.',
            'client_ids.*.exists'  => 'أحد العملاء المحددين غير موجود أو لا تملكه.',
            'tag_ids.required'     => 'يجب اختيار وسم واحد على الأقل.',
            'tag_ids.*.exists'     => 'أحد الوسوم المحددة غير موجود.',
            'action.required'      => 'نوع العملية مطلوب (assign أو remove).',
            'action.in'            => 'نوع العملية يجب أن يكون assign أو remove.',
        ];
    }
}
