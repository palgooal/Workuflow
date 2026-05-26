<?php

namespace App\Modules\CRM\Requests;

use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client && $this->user()->can('update', $client);
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'name'    => ['sometimes', 'required', 'string', 'max:100'],
            'phone'   => ['sometimes', 'nullable', 'string', 'max:30'],
            'email'   => [
                'sometimes',
                'nullable',
                'email',
                'max:150',
                Rule::unique('clients', 'email')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at')
                    ->ignore($clientId),
            ],
            'company' => ['sometimes', 'nullable', 'string', 'max:100'],
            'notes'   => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status'  => [
                'sometimes',
                'nullable',
                Rule::in(ClientStatus::values()),
            ],
            'source'  => [
                'sometimes',
                'nullable',
                Rule::in(ClientSource::values()),
            ],
            'is_active'   => ['sometimes', 'nullable', 'boolean'],
            'is_archived' => ['sometimes', 'boolean'],
            'position'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'website'     => ['sometimes', 'nullable', 'url', 'max:255'],
            'address'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'        => ['sometimes', 'nullable', 'string', 'max:100'],
            'country'     => ['sometimes', 'nullable', 'string', 'max:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم العميل مطلوب.',
            'name.max'      => 'اسم العميل لا يتجاوز 100 حرف.',
            'email.email'   => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'  => 'يوجد عميل بنفس البريد الإلكتروني.',
            'status.in'     => 'حالة العميل غير صحيحة.',
            'source.in'     => 'مصدر العميل غير صحيح.',
        ];
    }
}
