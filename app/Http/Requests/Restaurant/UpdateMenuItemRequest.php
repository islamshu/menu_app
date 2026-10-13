<?php

namespace App\Http\Requests\Restaurant;

class UpdateMenuItemRequest extends StoreMenuItemRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_image' => ['sometimes', 'boolean'],
        ]);
    }
}
