<?php

namespace App\Http\Requests\Restaurant;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_image' => ['sometimes', 'boolean'],
        ]);
    }
}
