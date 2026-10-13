<?php

namespace App\Http\Requests\Restaurant;

class ToggleRequest extends RestaurantFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field' => ['required', 'string'],
            'value' => ['required', 'boolean'],
        ];
    }
}
