<?php

namespace App\Modules\CRM\Requests;

use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Client::class);
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:100'],
            'payment_name' => ['nullable', 'string', 'max:100', 'ascii'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => [
                'nullable',
                'email',
                'max:150',
                // unique per user only (different users may share same client email)
                Rule::unique('clients', 'email')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],
            'company' => ['nullable', 'string', 'max:100'],
            'notes'   => ['nullable', 'string', 'max:2000'],
            // الفورم يرسل دائماً قيمة افتراضية (prospect) عبر <select required> —
            // required هنا يوحّد التحقق مع النموذج بدل ترك تناقض شكل/تحقق
            'status'  => [
                'required',
                Rule::in(ClientStatus::values()),
            ],
            'source'  => [
                'nullable',
                Rule::in(ClientSource::values()),
            ],
            'position' => ['nullable', 'string', 'max:100'],
            'website'  => ['nullable', 'url', 'max:255'],
            'address'  => ['nullable', 'string', 'max:255'],
            'city'     => ['nullable', 'string', 'max:100'],
            'country'  => ['nullable', 'string', 'max:2'],
            'is_active' => ['nullable', 'boolean'],

            // الوسوم الاختيارية عند الإنشاء
            // مسموح فقط بوسوم المستخدم الخاصة (user_id = هو) أو الوسوم النظامية المشتركة (user_id = NULL)
            // لمنع ربط وسوم خاصة بمستخدم آخر (IDOR)
            'tag_ids'   => ['nullable', 'array'],
            'tag_ids.*' => [
                'integer',
                Rule::exists('client_tags', 'id')->where(
                    fn ($query) => $query->where(
                        fn ($q) => $q->where('user_id', $this->user()->id)->orWhereNull('user_id')
                    )
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم العميل مطلوب.',
            'name.max'       => 'اسم العميل لا يتجاوز 100 حرف.',
            'payment_name.ascii' => 'الاسم البديل يجب أن يكون بأحرف إنجليزية فقط (يُستخدم في الدفع الإلكتروني).',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'   => 'يوجد عميل بنفس البريد الإلكتروني.',
            'status.required'=> 'حالة العميل مطلوبة.',
            'status.in'      => 'حالة العميل غير صحيحة.',
            'source.in'      => 'مصدر العميل غير صحيح.',
            'tag_ids.array'  => 'يجب أن تكون الوسوم مصفوفة.',
            'tag_ids.*.exists' => 'أحد الوسوم المحددة غير موجود أو لا تملكه.',
        ];
    }
}
