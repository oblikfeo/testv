import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Inter"', '"Segoe UI"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
            },
            colors: {
                ink: {
                    950: '#050507',
                    900: '#0a0a0f',
                    850: '#0e0e15',
                    800: '#13131c',
                },
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '33%': { transform: 'translate(3%, -4%) scale(1.05)' },
                    '66%': { transform: 'translate(-3%, 3%) scale(0.97)' },
                },
                'float-slow': {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '50%': { transform: 'translate(-4%, 4%) scale(1.08)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '200% 0' },
                    '100%': { backgroundPosition: '-200% 0' },
                },
                'pulse-glow': {
                    '0%, 100%': { opacity: 1, filter: 'brightness(1)' },
                    '50%': { opacity: 0.85, filter: 'brightness(1.25)' },
                },
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
                'gradient-x': {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
            },
            animation: {
                float: 'float 18s ease-in-out infinite',
                'float-slow': 'float-slow 24s ease-in-out infinite',
                shimmer: 'shimmer 2.5s linear infinite',
                'pulse-glow': 'pulse-glow 2.4s ease-in-out infinite',
                marquee: 'marquee 28s linear infinite',
                'gradient-x': 'gradient-x 6s ease infinite',
            },
            boxShadow: {
                glow: '0 0 40px -8px rgba(220, 38, 38, 0.55)',
                'glow-lg': '0 0 80px -12px rgba(220, 38, 38, 0.6)',
                'glow-fuchsia': '0 0 40px -8px rgba(217, 70, 239, 0.45)',
            },
            backgroundImage: {
                'grid-pattern': 'linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px)',
            },
        },
    },

    plugins: [forms],
};
