<?php

namespace App\Http\Requests\Restaurant;

use App\Models\Restaurant;
use Illuminate\Validation\Rule;

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
        $hex = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];

        return [
            'preset_key' => ['required', 'string', Rule::in(array_keys(config('qr.presets', [])))],
            'error_level' => ['required', 'string', Rule::in(array_keys(config('qr.error_levels', [])))],
            'module_style' => ['required', 'string', Rule::in(array_keys(config('qr.module_styles', [])))],
            'eye_style' => ['required', 'string', Rule::in(array_keys(config('qr.eye_styles', [])))],
            'frame_style' => ['required', 'string', Rule::in(array_keys(config('qr.frame_styles', [])))],
            'quiet_zone' => ['required', 'integer', 'min:0', 'max:8'],
            'foreground_color' => $hex,
            'background_color' => $hex,
            'eye_color' => $hex,
            'frame_label' => ['nullable', 'string', 'max:60'],
            'logo_enabled' => ['boolean'],
            'logo_ratio' => ['required', 'numeric', 'min:0.1', 'max:0.3'],
        ];
    }
}
