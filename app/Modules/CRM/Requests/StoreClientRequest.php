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
            'status'  => [
                'nullable',
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
            'tag_ids'   => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:client_tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم العميل مطلوب.',
            'name.max'       => 'اسم العميل لا يتجاوز 100 حرف.',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'   => 'يوجد عميل بنفس البريد الإلكتروني.',
            'status.in'      => 'حالة العميل غير صحيحة.',
            'source.in'      => 'مصدر العميل غير صحيح.',
            'tag_ids.array'  => 'يجب أن تكون الوسوم مصفوفة.',
        ];
    }
}
