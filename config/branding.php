<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Menu themes
    |--------------------------------------------------------------------------
    |
    | The single source of truth for the premium menu themes. Each theme ships
    | a complete default settings set that acts as both the initial branding
    | for a restaurant and the baseline "Reset to theme defaults" target.
    |
    | The keys must stay in sync with resources/js/Themes/registry.js.
    |
    */

    'default_theme' => 'noir-luxe',

    'themes' => [
        'noir-luxe' => [
            'label' => [
                'ar' => 'نوار لوكس',
                'en' => 'Noir Luxe',
            ],
            'description' => [
                'ar' => 'قائمة داكنة فاخرة بلمسات ذهبية وخطوط كلاسيكية أنيقة.',
                'en' => 'A dark, luxurious menu with gold accents and refined serif type.',
            ],
            'preview' => [
                'from' => '#0c0c0f',
                'to' => '#c8a349',
            ],
            'defaults' => [
                'theme_key' => 'noir-luxe',
                'primary_color' => '#c8a349',
                'secondary_color' => '#8a6f2e',
                'accent_color' => '#e7cd8f',
                'background_color' => '#0c0c0f',
                'text_color' => '#f4f1ea',
                'heading_font' => 'playfair',
                'body_font' => 'plex-arabic',
                'border_radius' => 'sm',
                'card_style' => 'elevated',
                'logo_display_mode' => 'circle',
            ],
        ],

        'atelier' => [
            'label' => [
                'ar' => 'أتيليه',
                'en' => 'Atelier',
            ],
            'description' => [
                'ar' => 'تصميم تحريري دافئ بخلفية عاجية ولمسات تراكوتا وخطوط راقية.',
                'en' => 'A warm editorial layout on ivory with terracotta accents.',
            ],
            'preview' => [
                'from' => '#f7f1e7',
                'to' => '#b4552d',
            ],
            'defaults' => [
                'theme_key' => 'atelier',
                'primary_color' => '#b4552d',
                'secondary_color' => '#6f7a4b',
                'accent_color' => '#d98a5f',
                'background_color' => '#f7f1e7',
                'text_color' => '#2c2622',
                'heading_font' => 'cormorant',
                'body_font' => 'dm-sans',
                'border_radius' => 'xl',
                'card_style' => 'outline',
                'logo_display_mode' => 'rounded',
            ],
        ],

        'pulse' => [
            'label' => [
                'ar' => 'بالس',
                'en' => 'Pulse',
            ],
            'description' => [
                'ar' => 'تصميم جريء سريع الإيقاع بخطوط عريضة وألوان نابضة وواجهة حركية.',
                'en' => 'A bold, fast-casual layout with punchy type and vivid colour.',
            ],
            'preview' => [
                'from' => '#ffffff',
                'to' => '#ff4d1c',
            ],
            'defaults' => [
                'theme_key' => 'pulse',
                'primary_color' => '#ff4d1c',
                'secondary_color' => '#1f2430',
                'accent_color' => '#ffd23f',
                'background_color' => '#ffffff',
                'text_color' => '#14161d',
                'heading_font' => 'space-grotesk',
                'body_font' => 'inter',
                'border_radius' => 'full',
                'card_style' => 'flat',
                'logo_display_mode' => 'square',
            ],
        ],

        'mosaic' => [
            'label' => [
                'ar' => 'فسيفساء',
                'en' => 'Mosaic',
            ],
            'description' => [
                'ar' => 'طابع أندلسي بزخارف هندسية وأقواس ومزيج تركوازي دافئ.',
                'en' => 'An Andalusian mood with geometric motifs, arches and warm teal.',
            ],
            'preview' => [
                'from' => '#f4ead7',
                'to' => '#0f6b5c',
            ],
            'defaults' => [
                'theme_key' => 'mosaic',
                'primary_color' => '#0f6b5c',
                'secondary_color' => '#b4532a',
                'accent_color' => '#d9a441',
                'background_color' => '#f4ead7',
                'text_color' => '#2b2118',
                'heading_font' => 'tajawal',
                'body_font' => 'cairo',
                'border_radius' => 'md',
                'card_style' => 'outline',
                'logo_display_mode' => 'rounded',
            ],
        ],

        'bistro' => [
            'label' => [
                'ar' => 'بيسترو',
                'en' => 'Bistro',
            ],
            'description' => [
                'ar' => 'قائمة مطبوعة كلاسيكية بعمودين وخطوط فاصلة منقّطة ولمسات خمريّة.',
                'en' => 'A classic printed menu with two columns, dotted leaders and bordeaux accents.',
            ],
            'preview' => [
                'from' => '#fbf7ee',
                'to' => '#8c1c2b',
            ],
            'defaults' => [
                'theme_key' => 'bistro',
                'primary_color' => '#8c1c2b',
                'secondary_color' => '#b8935a',
                'accent_color' => '#2f4f3e',
                'background_color' => '#fbf7ee',
                'text_color' => '#2a2118',
                'heading_font' => 'cormorant',
                'body_font' => 'dm-sans',
                'border_radius' => 'none',
                'card_style' => 'flat',
                'logo_display_mode' => 'circle',
            ],
        ],

        'neon' => [
            'label' => [
                'ar' => 'نيون',
                'en' => 'Neon',
            ],
            'description' => [
                'ar' => 'أجواء ليلية داكنة بتوهّج نيوني أرجواني وسماوي وعناوين عريضة.',
                'en' => 'A dark nightlife vibe with neon magenta/cyan glow and heavy type.',
            ],
            'preview' => [
                'from' => '#07070c',
                'to' => '#ff2d95',
            ],
            'defaults' => [
                'theme_key' => 'neon',
                'primary_color' => '#ff2d95',
                'secondary_color' => '#00e5ff',
                'accent_color' => '#c6ff00',
                'background_color' => '#07070c',
                'text_color' => '#f5f5ff',
                'heading_font' => 'space-grotesk',
                'body_font' => 'inter',
                'border_radius' => 'full',
                'card_style' => 'outline',
                'logo_display_mode' => 'square',
            ],
        ],

        'orchard' => [
            'label' => [
                'ar' => 'بستان',
                'en' => 'Orchard',
            ],
            'description' => [
                'ar' => 'تصميم عضوي منعش بشبكة صور متداخلة وزوايا دائرية وألوان خضراء ناعمة.',
                'en' => 'A fresh organic look with an interlocking image grid, soft rounded shapes and greens.',
            ],
            'preview' => [
                'from' => '#f3f6ec',
                'to' => '#4a7c2f',
            ],
            'defaults' => [
                'theme_key' => 'orchard',
                'primary_color' => '#4a7c2f',
                'secondary_color' => '#d97706',
                'accent_color' => '#a3b18a',
                'background_color' => '#f3f6ec',
                'text_color' => '#24301c',
                'heading_font' => 'figtree',
                'body_font' => 'cairo',
                'border_radius' => '2xl',
                'card_style' => 'elevated',
                'logo_display_mode' => 'rounded',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Colour palettes
    |--------------------------------------------------------------------------
    |
    | Ready-made colour sets the studio offers as one-click presets. Each set
    | only defines colours, so it can be layered on top of any theme.
    |
    |--------------------------------------------------------------------------
    */

    'palettes' => [
        [
            'key' => 'royal-gold',
            'label' => ['ar' => 'ذهبي ملكي', 'en' => 'Royal Gold'],
            'colors' => [
                'primary_color' => '#c8a349',
                'secondary_color' => '#8a6f2e',
                'accent_color' => '#e7cd8f',
                'background_color' => '#0c0c0f',
                'text_color' => '#f4f1ea',
            ],
        ],
        [
            'key' => 'emerald',
            'label' => ['ar' => 'زمردي', 'en' => 'Emerald'],
            'colors' => [
                'primary_color' => '#0f766e',
                'secondary_color' => '#115e59',
                'accent_color' => '#34d399',
                'background_color' => '#f8fafc',
                'text_color' => '#0f172a',
            ],
        ],
        [
            'key' => 'ruby',
            'label' => ['ar' => 'ياقوتي', 'en' => 'Ruby'],
            'colors' => [
                'primary_color' => '#b91c1c',
                'secondary_color' => '#7f1d1d',
                'accent_color' => '#fb923c',
                'background_color' => '#fff7ed',
                'text_color' => '#1c1917',
            ],
        ],
        [
            'key' => 'midnight',
            'label' => ['ar' => 'أزرق ليلي', 'en' => 'Midnight'],
            'colors' => [
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e40af',
                'accent_color' => '#60a5fa',
                'background_color' => '#0b1220',
                'text_color' => '#e5eaf3',
            ],
        ],
        [
            'key' => 'charcoal',
            'label' => ['ar' => 'فحمي', 'en' => 'Charcoal'],
            'colors' => [
                'primary_color' => '#334155',
                'secondary_color' => '#1e293b',
                'accent_color' => '#94a3b8',
                'background_color' => '#f1f5f9',
                'text_color' => '#0f172a',
            ],
        ],
        [
            'key' => 'violet',
            'label' => ['ar' => 'بنفسجي', 'en' => 'Violet'],
            'colors' => [
                'primary_color' => '#7c3aed',
                'secondary_color' => '#5b21b6',
                'accent_color' => '#c4b5fd',
                'background_color' => '#faf5ff',
                'text_color' => '#1e1b4b',
            ],
        ],
        [
            'key' => 'olive',
            'label' => ['ar' => 'زيتوني', 'en' => 'Olive'],
            'colors' => [
                'primary_color' => '#4d7c0f',
                'secondary_color' => '#365314',
                'accent_color' => '#a3e635',
                'background_color' => '#f7fee7',
                'text_color' => '#1a2e05',
            ],
        ],
        [
            'key' => 'rose-noir',
            'label' => ['ar' => 'وردي داكن', 'en' => 'Rose Noir'],
            'colors' => [
                'primary_color' => '#e11d48',
                'secondary_color' => '#881337',
                'accent_color' => '#fda4af',
                'background_color' => '#1c1917',
                'text_color' => '#fafaf9',
            ],
        ],
        [
            'key' => 'teal',
            'label' => ['ar' => 'تركوازي', 'en' => 'Teal'],
            'colors' => [
                'primary_color' => '#0d9488',
                'secondary_color' => '#134e4a',
                'accent_color' => '#5eead4',
                'background_color' => '#ffffff',
                'text_color' => '#0f172a',
            ],
        ],
        [
            'key' => 'copper',
            'label' => ['ar' => 'نحاسي', 'en' => 'Copper'],
            'colors' => [
                'primary_color' => '#b45309',
                'secondary_color' => '#78350f',
                'accent_color' => '#fbbf24',
                'background_color' => '#fefce8',
                'text_color' => '#1c1917',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | A curated allowlist of font pairings. `stack` is applied through the
    | `--brand-font-*` CSS variables and `bunny` is the font-family segment
    | used to build the single bunny.net request for the active menu only.
    |
    */

    'fonts' => [
        'playfair' => [
            'label' => 'بلايفير (كلاسيكي)',
            'stack' => "'Playfair Display', 'IBM Plex Sans Arabic', serif",
            'bunny' => 'playfair-display:500,600,700',
        ],
        'cormorant' => [
            'label' => 'كورمورانت (راقٍ)',
            'stack' => "'Cormorant Garamond', 'IBM Plex Sans Arabic', serif",
            'bunny' => 'cormorant-garamond:500,600,700',
        ],
        'plex-arabic' => [
            'label' => 'آي بي إم بلكس عربي',
            'stack' => "'IBM Plex Sans Arabic', sans-serif",
            'bunny' => 'ibm-plex-sans-arabic:400,500,600,700',
        ],
        'cairo' => [
            'label' => 'القاهرة',
            'stack' => "'Cairo', 'IBM Plex Sans Arabic', sans-serif",
            'bunny' => 'cairo:400,500,600,700',
        ],
        'tajawal' => [
            'label' => 'تجوّل',
            'stack' => "'Tajawal', 'IBM Plex Sans Arabic', sans-serif",
            'bunny' => 'tajawal:400,500,700',
        ],
        'inter' => [
            'label' => 'إنتر',
            'stack' => "'Inter', 'IBM Plex Sans Arabic', sans-serif",
            'bunny' => 'inter:400,500,600,700',
        ],
        'dm-sans' => [
            'label' => 'دي إم سانس',
            'stack' => "'DM Sans', 'IBM Plex Sans Arabic', sans-serif",
            'bunny' => 'dm-sans:400,500,700',
        ],
        'space-grotesk' => [
            'label' => 'سبيس جروتيسك (عريض)',
            'stack' => "'Space Grotesk', 'IBM Plex Sans Arabic', sans-serif",
            'bunny' => 'space-grotesk:500,600,700',
        ],
        'figtree' => [
            'label' => 'فيج تري',
            'stack' => "'Figtree', 'IBM Plex Sans Arabic', sans-serif",
            'bunny' => 'figtree:400,500,600',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Border radius
    |--------------------------------------------------------------------------
    */

    'radii' => [
        'none' => ['label' => 'بدون', 'value' => '0px'],
        'sm' => ['label' => 'صغير', 'value' => '0.375rem'],
        'md' => ['label' => 'متوسط', 'value' => '0.5rem'],
        'lg' => ['label' => 'كبير', 'value' => '0.75rem'],
        'xl' => ['label' => 'كبير جداً', 'value' => '1rem'],
        '2xl' => ['label' => 'دائري', 'value' => '1.5rem'],
        'full' => ['label' => 'كبسولة', 'value' => '9999px'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Card styles & logo display modes
    |--------------------------------------------------------------------------
    */

    'card_styles' => [
        'elevated' => ['label' => 'بارز بظل'],
        'outline' => ['label' => 'محدد بإطار'],
        'flat' => ['label' => 'مسطح'],
        'solid' => ['label' => 'مصمت ملوّن'],
    ],

    'logo_modes' => [
        'circle' => ['label' => 'دائري'],
        'rounded' => ['label' => 'زوايا ناعمة'],
        'square' => ['label' => 'مربع'],
        'wordmark' => ['label' => 'كتابة فقط'],
        'hidden' => ['label' => 'مخفي'],
    ],
];
