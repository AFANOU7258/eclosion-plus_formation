import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            colors: {
                // ------------------------------------------------------------
                // 🎨 Palette "ECLOSION" — extraite du logo logo-header.png
                // ------------------------------------------------------------
                eclosion: {
                    // Vert principal (88.7% du logo)
                    50:  '#e8f5ec',
                    100: '#c8e7cf',
                    200: '#a4d8af',
                    300: '#7cc98c',
                    400: '#55b96a',
                    500: '#2ea948',     // accent vert vif
                    600: '#0d6025',     // 🔥 PRIMARY — vert foncé du logo
                    700: '#0b4f1e',
                    800: '#093e18',
                    900: '#062d11',
                    950: '#041c0a',
                },

                // Bleu secondaire (7% du logo)
                ocean: {
                    50:  '#e8f0fa',
                    100: '#c4d7f2',
                    200: '#9dbdea',
                    300: '#73a2e1',
                    400: '#4b87d8',
                    500: '#1e6dcf',
                    600: '#06429a',     // 🔥 SECONDARY — bleu profond du logo
                    700: '#05357d',
                    800: '#042860',
                    900: '#031b43',
                    950: '#020e25',
                },

                // Tons neutres dérivés de l'ambiance du logo
                cloud: {
                    50:  '#fdfefe',
                    100: '#f3f6fa',     // accent très clair présent dans le logo
                    200: '#e4e9f0',
                    300: '#cbd3dd',
                    400: '#a8b4c3',
                    500: '#8796a9',
                    600: '#6b7d93',
                    700: '#566578',
                    800: '#3f4b5a',
                    900: '#2a323d',
                    950: '#181d24',
                },
            },

            fontFamily: {
                sans: ['Inter', 'Nunito', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', 'Inter', ...defaultTheme.fontFamily.sans],
            },

            borderRadius: {
                '4xl': '2rem',
            },

            boxShadow: {
                'eclosion': '0 4px 14px 0 rgba(13, 96, 37, 0.15)',
                'eclosion-lg': '0 10px 40px 0 rgba(13, 96, 37, 0.12)',
            },
        },
    },

    plugins: [
        forms,
        typography,
    ],
};
