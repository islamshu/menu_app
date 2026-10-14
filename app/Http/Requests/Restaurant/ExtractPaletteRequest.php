<?php

namespace App\Http\Requests\Restaurant;

use App\Models\Restaurant;

class ExtractPaletteRequest extends RestaurantFormRequest
{
    public function authorize(): bool
    {
        $restaurant = $this->restaurant();

        return $restaurant instanceof Restaurant
            && $this->user() !== null
            && $this->user()->can('update', $restaurant);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
