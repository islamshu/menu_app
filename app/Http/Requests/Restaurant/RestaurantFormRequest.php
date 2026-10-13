<?php

namespace App\Http\Requests\Restaurant;

use App\Models\Restaurant;
use Illuminate\Foundation\Http\FormRequest;

abstract class RestaurantFormRequest extends FormRequest
{
    /**
     * The active tenant resolved for this request.
     */
    protected function restaurant(): ?Restaurant
    {
        $restaurant = app()->bound('active_restaurant')
            ? app('active_restaurant')
            : null;

        return $restaurant instanceof Restaurant ? $restaurant : null;
    }

    /**
     * @return array<int, string>
     */
    protected function locales(): array
    {
        return array_keys(config('locales.supported'));
    }

    public function authorize(): bool
    {
        $restaurant = $this->restaurant();

        return $restaurant !== null
            && $this->user() !== null
            && $this->user()->can('manageMenu', $restaurant);
    }
}
