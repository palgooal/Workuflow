import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                tajawal: ['Tajawal', 'sans-serif'],
                alexandria: ['Alexandria', 'sans-serif'],
                cairo: ['Cairo', 'sans-serif'],
            },
            colors: {
                brand:  '#320E8E',
                accent: '#14C698',
                text: '#0F172A',
                muted: '#475569',
                background: '#F8FAFC',
                surface: '#FFFFFF',
            },
        },
    },

    plugins: [forms],
};
