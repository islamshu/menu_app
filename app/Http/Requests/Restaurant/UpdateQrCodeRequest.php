<?php

namespace App\Http\Requests\Restaurant;

use App\Models\Restaurant;
use App\Services\Qr\QrSettings;

class UpdateQrCodeRequest extends RestaurantFormRequest
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
        return QrSettings::rules();
    }
}
