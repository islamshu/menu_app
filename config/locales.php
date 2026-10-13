<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported content locales
    |--------------------------------------------------------------------------
    |
    | Menu content (categories and items) may be provided in any of these
    | locales. The first entry is treated as the default fallback locale.
    | Adding a locale here is enough to expose it across validation, the
    | dashboard translation tabs and public menu rendering.
    |
    */

    'supported' => [
        'ar' => [
            'label' => 'العربية',
            'dir' => 'rtl',
        ],
        'en' => [
            'label' => 'English',
            'dir' => 'ltr',
        ],
    ],

    'default' => 'ar',
];
