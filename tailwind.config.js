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
                // الخط الافتراضي للداشبورد — Readex Pro (هوية دراهم)
                sans: ['"Readex Pro"', ...defaultTheme.fontFamily.sans],
                tajawal: ['Tajawal', 'sans-serif'],
                alexandria: ['Alexandria', 'sans-serif'],
                cairo: ['Cairo', 'sans-serif'],
            },
            colors: {
                // ===== نظام ألوان دراهم (مرجع design.md) =====
                // Primary — Deep Indigo
                brand: {
                    DEFAULT: '#310E8E',
                    50:  '#EEEAFB',
                    100: '#D8CEF4',
                    600: '#3A14A3',
                    700: '#290B77',
                    800: '#21095F',
                    900: '#180645',
                },
                // Secondary / accent — Teal Emerald
                accent: {
                    DEFAULT: '#13C597',
                    50:  '#E5FBF4',
                    100: '#C2F4E5',
                    600: '#0FA67F',
                    700: '#0C8567',
                },
                // النصوص والأسطح
                ink:     '#0E0E1A', // نص أساسي
                muted:   '#6B7280', // نص ثانوي / labels
                surface: '#FFFFFF', // البطاقات والحاويات
                // tints حالة ناعمة (design.md)
                success: { DEFAULT: '#059669', soft: '#D1FAE5' },
                error:   { DEFAULT: '#DC2626', soft: '#FEE2E2' },
            },
            backgroundColor: {
                page: '#F7F8FC', // خلفية الصفحة (off-white)
            },
            borderColor: {
                subtle: '#E5E7EB', // الفواصل وحدود البطاقات
            },
            borderRadius: {
                btn: '10px', // أزرار (design.md)
            },
            zIndex: {
                dropdown: '1000',
                sticky: '1010',
                overlay: '1020',
                modal: '1030',
                toast: '1040',
                tooltip: '1050',
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(14 14 26 / 0.04)',
                'card-hover': '0 4px 12px -2px rgb(14 14 26 / 0.08)',
                pop: '0 8px 24px -4px rgb(14 14 26 / 0.12)',
            },
        },
    },

    plugins: [forms],
};
