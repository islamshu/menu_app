<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR presets
    |--------------------------------------------------------------------------
    |
    | Five premium presets that mirror the menu themes. Backgrounds are kept
    | light and modules dark for maximum decoder compatibility (dark-on-light).
    |
    */

    'default_preset' => 'noir-luxe',

    'presets' => [
        'noir-luxe' => [
            'label' => ['ar' => 'نوار لوكس', 'en' => 'Noir Luxe'],
            'description' => ['ar' => 'داكن على كريمي بلمسة ذهبية', 'en' => 'Dark on cream with gold accent'],
            'defaults' => [
                'preset_key' => 'noir-luxe',
                'error_level' => 'Q',
                'module_style' => 'rounded',
                'eye_style' => 'rounded',
                'frame_style' => 'card',
                'quiet_zone' => 4,
                'foreground_color' => '#0c0c0f',
                'background_color' => '#f4f1ea',
                'eye_color' => '#6b5016',
                'frame_label' => '',
                'logo_enabled' => false,
                'logo_ratio' => 0.12,
            ],
        ],
        'atelier' => [
            'label' => ['ar' => 'أتيليه', 'en' => 'Atelier'],
            'description' => ['ar' => 'طابع تحريري أنيق', 'en' => 'Elegant editorial'],
            'defaults' => [
                'preset_key' => 'atelier',
                'error_level' => 'M',
                'module_style' => 'square',
                'eye_style' => 'square',
                'frame_style' => 'label',
                'quiet_zone' => 4,
                'foreground_color' => '#2c2622',
                'background_color' => '#f7f1e7',
                'eye_color' => '#b4552d',
                'frame_label' => '',
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
                'module_style' => 'dots',
                'eye_style' => 'circle',
                'frame_style' => 'card',
                'quiet_zone' => 4,
                'foreground_color' => '#14161d',
                'background_color' => '#ffffff',
                'eye_color' => '#e03a10',
                'frame_label' => '',
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
                'module_style' => 'rounded',
                'eye_style' => 'rounded',
                'frame_style' => 'label',
                'quiet_zone' => 4,
                'foreground_color' => '#2b2118',
                'background_color' => '#f4ead7',
                'eye_color' => '#0f6b5c',
                'frame_label' => '',
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
                'module_style' => 'diamond',
                'eye_style' => 'circle',
                'frame_style' => 'card',
                'quiet_zone' => 4,
                'foreground_color' => '#07070c',
                'background_color' => '#ffffff',
                'eye_color' => '#d81b74',
                'frame_label' => '',
                'logo_enabled' => false,
                'logo_ratio' => 0.12,
            ],
        ],
    ],

    'error_levels' => [
        'L' => ['label' => 'منخفض — ٧٪', 'recovery' => 0.07],
        'M' => ['label' => 'متوسط — ١٥٪', 'recovery' => 0.15],
        'Q' => ['label' => 'مرتفع — ٢٥٪', 'recovery' => 0.25],
        'H' => ['label' => 'أقصى — ٣٠٪', 'recovery' => 0.30],
    ],

    'module_styles' => [
        'square' => ['label' => 'مربّعات'],
        'rounded' => ['label' => 'زوايا ناعمة'],
        'dots' => ['label' => 'نقاط'],
        'diamond' => ['label' => 'معينات'],
    ],

    'eye_styles' => [
        'square' => ['label' => 'مربّعة'],
        'rounded' => ['label' => 'دائرية الزوايا'],
        'circle' => ['label' => 'دائريّة'],
    ],

    'frame_styles' => [
        'none' => ['label' => 'بدون إطار'],
        'label' => ['label' => 'اسم أسفل الرمز'],
        'card' => ['label' => 'بطاقة'],
        'ticket' => ['label' => 'تذكرة'],
    ],

    'sizes' => [
        'small' => 256,
        'medium' => 512,
        'large' => 1024,
        'print' => 2048,
    ],

    'logo' => [
        'max_ratio' => 0.15,
        'min_ratio' => 0.08,
    ],

    'defaults' => [
        'preset_key' => 'noir-luxe',
        'error_level' => 'Q',
        'module_style' => 'rounded',
        'eye_style' => 'rounded',
        'frame_style' => 'card',
        'quiet_zone' => 4,
        'foreground_color' => '#0c0c0f',
        'background_color' => '#f4f1ea',
        'eye_color' => '#6b5016',
        'frame_label' => '',
        'logo_enabled' => false,
        'logo_ratio' => 0.12,
    ],
];
