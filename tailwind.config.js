import defaultTheme from 'tailwindcss/defaultTheme';
import filamentPreset from './vendor/filament/filament/tailwind.config.preset.js';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [filamentPreset],
    content: [
        './app/Filament/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Hanken Grotesk', ...defaultTheme.fontFamily.sans],
                display: ['Spectral', 'Georgia', 'serif'],
                mono: ['IBM Plex Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    primary: '#6C2E63',
                    'primary-strong': '#4D2049',
                    'primary-soft': '#F4EAF1',
                    accent: '#E0A431',
                    'accent-strong': '#BF861A',
                    'accent-soft': '#FBF1DC',
                },
            },
            borderRadius: {
                card: '16px',
                btn: '10px',
                input: '10px',
                pill: '999px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms')({ strategy: 'class' }),
    ],
};
