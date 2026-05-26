<?php

namespace App\Modules\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('importClients', \App\Models\Client::class);
    }

    public function rules(): array
    {
        $maxKb    = config('crm.import.max_file_size_kb', 10240);
        $mimeList = config('crm.import.allowed_mimes', ['xlsx', 'csv', 'xls']);

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxKb}",
                'mimes:' . implode(',', $mimeList),
            ],

            // مفتاح التكرارية — يُرسل من الـ frontend بعد المعاينة
            'idempotency_key' => ['nullable', 'string', 'max:64'],

            // خيارات الاستيراد الاختيارية
            'skip_duplicates' => ['nullable', 'boolean'],
            'update_existing' => ['nullable', 'boolean'],

            // mapping الأعمدة (اختياري — إذا أرسل الـ frontend mapping مخصص)
            'column_map'          => ['nullable', 'array'],
            'column_map.name'     => ['nullable', 'string', 'max:60'],
            'column_map.phone'    => ['nullable', 'string', 'max:60'],
            'column_map.email'    => ['nullable', 'string', 'max:60'],
            'column_map.company'  => ['nullable', 'string', 'max:60'],
            'column_map.notes'    => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        $maxMb = (int) (config('crm.import.max_file_size_kb', 10240) / 1024);

        return [
            'file.required' => 'يجب رفع ملف الاستيراد.',
            'file.max'      => "حجم الملف لا يتجاوز {$maxMb} ميجابايت.",
            'file.mimes'    => 'صيغة الملف غير مدعومة — xlsx أو csv فقط.',
        ];
    }
}
