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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Dark mode colors
                'deep-blue': '#030712',
                'deep-blue-light': '#0f1419',
                
                // Light mode colors - Premium gradient palette
                'light-primary': '#ffffff',
                'light-secondary': '#f8fafb',
                'light-tertiary': '#f0f4f8',
                'light-accent': '#0066ff',
                'light-accent-hover': '#0052cc',
                
                // Soft white for dark mode
                'soft-white': '#f8f9fa',
                
                // Enhanced gray palette for light mode
                'light-gray-text': '#1a202c',
                'light-gray-muted': '#718096',
                'light-gray-border': '#e2e8f0',
            },
            backgroundImage: {
                // Light mode gradients
                'gradient-light': 'linear-gradient(135deg, #ffffff 0%, #f8fafb 100%)',
                'gradient-light-subtle': 'linear-gradient(180deg, #ffffff 0%, #f0f4f8 100%)',
                'gradient-accent': 'linear-gradient(135deg, #0066ff 0%, #0052cc 100%)',
            },
            transitionProperty: {
                colors: 'background-color, border-color, color, fill, stroke',
                all: 'all',
            },
            transitionDuration: {
                DEFAULT: '300ms',
                'fast': '150ms',
                'slow': '500ms',
            },
            transitionTimingFunction: {
                DEFAULT: 'cubic-bezier(0.4, 0, 0.2, 1)',
                'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
                'bounce': 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
            },
            boxShadow: {
                // Light mode shadows
                'light-sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'light-md': '0 4px 6px -1px rgba(0, 0, 0, 0.08)',
                'light-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                'light-xl': '0 20px 25px -5px rgba(0, 0, 0, 0.12)',
                
                // Dark mode shadows
                'dark-md': '0 4px 6px -1px rgba(0, 0, 0, 0.3)',
                'dark-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.5)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-down': 'slideDown 0.3s ease-out',
                'pulse-soft': 'pulseSoft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'theme-switch': 'themeSwitch 0.5s ease-in-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.8' },
                },
                themeSwitch: {
                    '0%': { opacity: '0.7', transform: 'scale(0.95)' },
                    '50%': { opacity: '0.5' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
            },
        },
    },

    plugins: [forms],
};
