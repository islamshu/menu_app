<?php

namespace App\Http\Requests\Restaurant;

class StoreMenuItemRequest extends RestaurantFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'array'],
            'description' => ['nullable', 'array'],
            'menu_category_id' => ['nullable', 'integer'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_popular' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        foreach ($this->locales() as $locale) {
            $rules["name.{$locale}"] = ['required', 'string', 'max:150'];
            $rules["description.{$locale}"] = ['nullable', 'string', 'max:1500'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'price' => 'السعر',
            'compare_at_price' => 'السعر قبل الخصم',
            'menu_category_id' => 'القسم',
        ];

        foreach ($this->locales() as $locale) {
            $attributes["name.{$locale}"] = "الاسم ($locale)";
            $attributes["description.{$locale}"] = "الوصف ($locale)";
        }

        return $attributes;
    }
}
