<?php

namespace App\Http\Requests\Admin;

use App\Enums\BillingInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    /**
     * The admin area is already gated by the super_admin middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_interval' => ['required', Rule::enum(BillingInterval::class)],
            'is_active' => ['boolean'],
            'features' => ['array'],
            'features.*' => ['string', 'max:100'],
            'limits' => ['array'],
            'limits.restaurants' => ['nullable', 'integer', 'min:0'],
            'limits.menu_items' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الباقة',
            'price' => 'السعر',
            'billing_interval' => 'دورة الفوترة',
        ];
    }
}
