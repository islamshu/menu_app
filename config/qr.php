<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default preset
    |--------------------------------------------------------------------------
    |
    | The preset applied to restaurants that never customised their QR code.
    |
    */

    'default_preset' => 'noir-luxe',

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    |
    | Five premium presets that mirror the menu themes. Backgrounds are kept
    | light and modules dark for maximum decoder compatibility (dark-on-light).
    | Only settings supported by the server-side renderer (endroid/qr-code)
    | are exposed: colours, error-correction level, quiet zone and logo.
    |
    */

    'presets' => [
        'noir-luxe' => [
            'label' => ['ar' => 'نوار لوكس', 'en' => 'Noir Luxe'],
            'description' => ['ar' => 'داكن على كريمي بلمسة ذهبية', 'en' => 'Dark on cream with gold accent'],
            'defaults' => [
                'preset_key' => 'noir-luxe',
                'error_level' => 'Q',
                'quiet_zone' => 4,
                'foreground_color' => '#0c0c0f',
                'background_color' => '#f4f1ea',
                'logo_enabled' => false,
                'logo_ratio' => 0.12,
            ],
        ],
        'atelier' => [
            'label' => ['ar' => 'أتليه', 'en' => 'Atelier'],
            'description' => ['ar' => 'طابع أندلسي دافئ', 'en' => 'Warm editorial'],
            'defaults' => [
                'preset_key' => 'atelier',
                'error_level' => 'Q',
                'quiet_zone' => 4,
                'foreground_color' => '#2c2622',
                'background_color' => '#f7f1e7',
                'logo_enabled' => false,
                'logo_ratio' => 0.12,
            ],
        ],
        'pulse' => [
            'label' => ['ar' => 'بالس', 'en' => 'Pulse'],
            'description' => ['ar' => 'جريء ونابض', 'en' => 'Bold and punchy'],
            'defaults' => [
                'preset_key' => 'pulse',
                'error_level' => 'H',
                'quiet_zone' => 4,
                'foreground_color' => '#14161d',
                'background_color' => '#ffffff',
                'logo_enabled' => false,
                'logo_ratio' => 0.12,
            ],
        ],
        'mosaic' => [
            'label' => ['ar' => 'فسيفساء', 'en' => 'Mosaic'],
            'description' => ['ar' => 'طابع أندلسي دافئ', 'en' => 'Warm Andalusian mood'],
            'defaults' => [
                'preset_key' => 'mosaic',
                'error_level' => 'Q',
                'quiet_zone' => 4,
                'foreground_color' => '#2b2118',
                'background_color' => '#f4ead7',
                'logo_enabled' => false,
                'logo_ratio' => 0.12,
            ],
        ],
        'neon' => [
            'label' => ['ar' => 'نيون', 'en' => 'Neon'],
            'description' => ['ar' => 'ليلي بتوهج نيوني', 'en' => 'Dark-night neon glow'],
            'defaults' => [
                'preset_key' => 'neon',
                'error_level' => 'H',
                'quiet_zone' => 4,
                'foreground_color' => '#07070c',
                'background_color' => '#ffffff',
                'logo_enabled' => false,
                'logo_ratio' => 0.12,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error correction levels
    |--------------------------------------------------------------------------
    */

    'error_levels' => [
        'L' => ['label' => 'منخفض — ٧٪', 'recovery' => 0.07],
        'M' => ['label' => 'متوسط — ١٥٪', 'recovery' => 0.15],
        'Q' => ['label' => 'مرتفع — ٢٥٪', 'recovery' => 0.25],
        'H' => ['label' => 'أقصى — ٣٠٪', 'recovery' => 0.30],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export sizes (pixels)
    |--------------------------------------------------------------------------
    */

    'sizes' => [
        'small' => 256,
        'medium' => 512,
        'large' => 1024,
        'print' => 2048,
    ],

    /*
    |--------------------------------------------------------------------------
    | Render tuning
    |--------------------------------------------------------------------------
    */

    'render' => [
        'preview_size' => 512,
        'download_size' => 1024,
        'max_size' => 2048,
        'min_size' => 128,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo constraints
    |--------------------------------------------------------------------------
    |
    | The logo is expressed as a ratio of the QR width. Keeping it below the
    | maximum keeps a logo-bearing code readable by phone cameras.
    |
    */

    'logo' => [
        'max_ratio' => 0.15,
        'min_ratio' => 0.08,
    ],

    'defaults' => [
        'preset_key' => 'noir-luxe',
        'error_level' => 'Q',
        'quiet_zone' => 4,
        'foreground_color' => '#0c0c0f',
        'background_color' => '#f4f1ea',
        'logo_enabled' => false,
        'logo_ratio' => 0.12,
    ],
];
