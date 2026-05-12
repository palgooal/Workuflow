<?php

namespace App\Http\Requests\Categories;

use App\Support\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:50'],
            'type'  => ['required', new Enum(TransactionType::class)],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon'  => ['required', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم الفئة مطلوب.',
            'name.max'       => 'اسم الفئة يجب ألا يتجاوز 50 حرفاً.',
            'type.required'  => 'نوع الفئة مطلوب.',
            'type.enum'      => 'نوع الفئة غير صالح.',
            'color.required' => 'لون الفئة مطلوب.',
            'color.regex'    => 'صيغة اللون غير صحيحة.',
            'icon.required'  => 'أيقونة الفئة مطلوبة.',
        ];
    }
}
