<?php

namespace App\Http\Requests\Admin;

use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
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
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'restaurant_id' => 'المطعم',
            'plan_id' => 'الباقة',
            'status' => 'الحالة',
            'starts_at' => 'تاريخ البداية',
            'ends_at' => 'تاريخ الانتهاء',
        ];
    }
}
