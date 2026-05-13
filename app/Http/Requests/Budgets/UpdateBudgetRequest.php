<?php

namespace App\Http\Requests\Budgets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount'      => ['required', 'numeric', 'min:1', 'max:999999999'],
            'period'      => ['required', 'in:monthly,yearly'],
            'year'        => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'       => ['required_if:period,monthly', 'nullable', 'integer', 'min:1', 'max:12'],
            'category_id' => ['nullable', 'string', 'exists:categories,id'],
            'project_id'  => ['nullable', 'string', 'exists:projects,id'],
        ];
    }
}
