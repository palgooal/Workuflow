<?php

namespace App\Http\Requests\Projects;

use App\Support\Enums\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProjectRequest extends FormRequest
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
            'currency'    => ['required', 'string', 'in:SAR,USD,EUR,GBP,AED,KWD'],
            'color'       => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['nullable', 'boolean'],
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
