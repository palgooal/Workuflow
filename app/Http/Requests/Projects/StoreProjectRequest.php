<?php

namespace App\Http\Requests\Projects;

use App\Support\Enums\ProjectStatus;
use App\Support\Enums\ProjectType;
use App\Support\Helpers\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'type'        => ['required', new Enum(ProjectType::class)],
            'currency'    => ['required', 'string', Rule::in(Currency::codes())],
            'color'       => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['nullable', new Enum(ProjectStatus::class)],
            'client_id'   => ['nullable', 'integer', 'exists:clients,id'],
            'contract_value' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            // services[]: [{service_id, amount, type, notes}]
            'services'              => ['nullable', 'array'],
            'services.*.service_id' => ['required_with:services', 'integer', 'exists:services,id'],
            'services.*.amount'     => ['required_with:services', 'numeric', 'min:0'],
            'services.*.type'       => ['required_with:services', 'in:income'],
            'services.*.notes'              => ['nullable', 'string', 'max:255'],
            'services.*.target_margin_pct'  => ['nullable', 'integer', 'min:1', 'max:99'],
            // منفذو الخدمة (متعددون)
            'services.*.members'                      => ['nullable', 'array'],
            'services.*.members.*.team_member_id'     => ['required_with:services.*.members', 'string', 'exists:team_members,id'],
            'services.*.members.*.team_cost'          => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'اسم المشروع مطلوب.',
            'name.max'          => 'اسم المشروع يجب ألا يتجاوز 100 حرف.',
            'type.required'     => 'نوع المشروع مطلوب.',
            'type.enum'         => 'نوع المشروع غير صالح.',
            'currency.required' => 'العملة مطلوبة.',
            'currency.in'       => 'العملة المختارة غير مدعومة.',
            'color.required'    => 'لون المشروع مطلوب.',
            'color.regex'       => 'صيغة اللون غير صحيحة.',
            'description.max'   => 'الوصف يجب ألا يتجاوز 500 حرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'اسم المشروع',
            'type'        => 'نوع المشروع',
            'currency'    => 'العملة',
            'color'       => 'اللون',
            'description' => 'الوصف',
        ];
    }
}
