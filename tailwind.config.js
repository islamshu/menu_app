import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    '"IBM Plex Sans Arabic"',
                    'Figtree',
                    ...defaultTheme.fontFamily.sans,
                ],
                display: [
                    '"Playfair Display"',
                    '"IBM Plex Sans Arabic"',
                    ...defaultTheme.fontFamily.serif,
                ],
            },

            colors: {
                brand: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },

                // Public menu — original "editorial ember" identity.
                paper: '#fbf9f6',
                cream: '#f3ece1',
                ink: {
                    DEFAULT: '#1c1917',
                    soft: '#44403c',
                    muted: '#78716c',
                    faint: '#a8a29e',
                    line: '#e7e1d8',
                },
                ember: {
                    50: '#fdf6f1',
                    100: '#f9e7db',
                    200: '#f0cbb4',
                    300: '#e4a883',
                    400: '#d5824f',
                    500: '#c4622c',
                    600: '#a94d20',
                    700: '#8b3b1b',
                    800: '#6f2f1a',
                    900: '#5b2818',
                },
            },

            boxShadow: {
                card: '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.06)',
                'card-dark':
                    '0 1px 2px 0 rgb(0 0 0 / 0.3), 0 1px 3px 0 rgb(0 0 0 / 0.4)',
                pop: '0 10px 30px -12px rgb(15 23 42 / 0.25)',
                editor: '0 24px 60px -32px rgb(28 25 23 / 0.45)',
                lift: '0 30px 60px -30px rgb(28 25 23 / 0.35)',
            },

            keyframes: {
                'fade-in': {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                'slide-up': {
                    from: { opacity: '0', transform: 'translateY(8px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-in': {
                    from: { opacity: '0', transform: 'translateX(-12px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(-100%)' },
                },
                'ken-burns': {
                    from: { transform: 'scale(1.05)' },
                    to: { transform: 'scale(1.15)' },
                },
                'fade-up': {
                    from: { opacity: '0', transform: 'translateY(16px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
            },

            animation: {
                'fade-in': 'fade-in 0.2s ease-out',
                'slide-up': 'slide-up 0.25s ease-out',
                'slide-in': 'slide-in 0.25s ease-out',
                shimmer: 'shimmer 1.5s infinite',
                'ken-burns': 'ken-burns 18s ease-out forwards',
                'fade-up': 'fade-up 0.5s cubic-bezier(0.16, 1, 0.3, 1) both',
            },
        },
    },

    plugins: [forms],
};
