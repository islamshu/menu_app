<?php

namespace App\Http\Requests\Restaurant;

use App\Models\Restaurant;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends RestaurantFormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'default_locale' => ['required', Rule::in($this->locales())],
            'timezone' => ['required', 'timezone'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_cover' => ['sometimes', 'boolean'],
        ];
    }
}
