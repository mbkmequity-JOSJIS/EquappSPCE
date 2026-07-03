/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                equapp: {
                    navy: '#122848',
                    blue: '#0055A0',
                    sky: '#438BC4',
                    ice: '#8CC1E9',
                    stone: '#AAA6A0',
                    warn: '#FFA723',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Montserrat', 'Quicksand', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                glass: '0 8px 32px rgba(0, 85, 160, 0.25), 0 0 0 1px rgba(255,255,255,0.08) inset',
                neon: '0 0 24px rgba(140, 193, 233, 0.45)',
                'neon-warn': '0 0 18px rgba(255, 167, 35, 0.55)',
            },
        },
    },
    plugins: [],
};
