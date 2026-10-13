<?php

namespace App\Http\Requests\Restaurant;

class ReorderRequest extends RestaurantFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer', 'distinct'],
        ];
    }
}
