<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\CRM\Models\ClientTag::class);
    }

    public function rules(): array
    {
        return [
            'name'  => [
                'required',
                'string',
                'max:50',
            ],
            'color' => [
                'required',
                'string',
                'regex:/^#([A-Fa-f0-9]{6})$/',  // HEX فقط مثل #10B981
            ],
            'icon'  => ['nullable', 'string', 'max:10'],
            'slug'  => [
                'nullable',
                'string',
                'max:60',
                'alpha_dash',
                // unique per user
                Rule::unique('client_tags', 'slug')
                    ->where('user_id', $this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم الوسم مطلوب.',
            'name.max'       => 'اسم الوسم لا يتجاوز 50 حرفاً.',
            'color.required' => 'لون الوسم مطلوب.',
            'color.regex'    => 'اللون يجب أن يكون HEX مثل #10B981.',
            'slug.unique'    => 'هذا الـ slug مستخدم مسبقاً.',
            'slug.alpha_dash'=> 'الـ slug يقبل حروفاً وأرقاماً وشرطات فقط.',
        ];
    }
}
