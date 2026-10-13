<?php

namespace App\Http\Requests\Restaurant;

class StoreCategoryRequest extends RestaurantFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'array'],
            'description' => ['nullable', 'array'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        foreach ($this->locales() as $locale) {
            $rules["name.{$locale}"] = ['required', 'string', 'max:120'];
            $rules["description.{$locale}"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->locales() as $locale) {
            $attributes["name.{$locale}"] = "الاسم ($locale)";
            $attributes["description.{$locale}"] = "الوصف ($locale)";
        }

        return $attributes;
    }
}
