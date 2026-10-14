<?php

namespace App\Http\Requests\Restaurant;

use App\Models\Restaurant;
use Illuminate\Validation\Rule;

class UpdateBrandingRequest extends RestaurantFormRequest
{
    public function authorize(): bool
    {
        $restaurant = $this->restaurant();

        return $restaurant instanceof Restaurant
            && $this->user() !== null
            && $this->user()->can('update', $restaurant);
    }

    /**
     * Only allow-listed theme settings may ever be persisted.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hex = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];

        return [
            'theme_key' => ['required', 'string', Rule::in(array_keys(config('branding.themes')))],
            'primary_color' => $hex,
            'secondary_color' => $hex,
            'accent_color' => $hex,
            'background_color' => $hex,
            'text_color' => $hex,
            'heading_font' => ['required', 'string', Rule::in(array_keys(config('branding.fonts')))],
            'body_font' => ['required', 'string', Rule::in(array_keys(config('branding.fonts')))],
            'border_radius' => ['required', 'string', Rule::in(array_keys(config('branding.radii')))],
            'card_style' => ['required', 'string', Rule::in(array_keys(config('branding.card_styles')))],
            'logo_display_mode' => ['required', 'string', Rule::in(array_keys(config('branding.logo_modes')))],
        ];
    }
}
