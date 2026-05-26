<?php

namespace App\Modules\CRM\Requests;

use App\Modules\CRM\Enums\PortalPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortalTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('managePortal', \App\Models\Client::class);
    }

    public function rules(): array
    {
        $validPermissions = array_column(PortalPermission::cases(), 'value');

        return [
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],

            'permissions'   => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::in($validPermissions),
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after:now',
                'before:' . now()->addYear()->toDateString(),
            ],

            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'    => 'يجب تحديد العميل.',
            'client_id.exists'      => 'العميل غير موجود أو لا تملكه.',
            'permissions.*.in'      => 'إحدى الصلاحيات المحددة غير صحيحة.',
            'expires_at.after'      => 'تاريخ الانتهاء يجب أن يكون في المستقبل.',
            'expires_at.before'     => 'تاريخ الانتهاء لا يتجاوز سنة من الآن.',
        ];
    }

    /**
     * قيم افتراضية — الصلاحيات تُأخذ من PortalPermission::defaults() إن لم تُرسل
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('permissions')) {
            $this->merge([
                'permissions' => PortalPermission::defaults(),
            ]);
        }
    }
}
