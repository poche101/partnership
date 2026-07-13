/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                serif: ['"Fraunces"', 'serif'],
                sans: ['"Inter"', 'sans-serif'],
                mono: ['"IBM Plex Mono"', 'monospace'],
            },
            colors: {
                border: '#E4DFD3',
                background: '#F6F1E7',
                foreground: '#16233A',
                muted: {
                    DEFAULT: '#EFE9DC',
                    foreground: '#6B7280',
                },
                card: {
                    DEFAULT: '#FFFFFF',
                    foreground: '#16233A',
                },
                primary: {
                    DEFAULT: '#16233A',
                    foreground: '#F6F1E7',
                },
                accent: {
                    DEFAULT: '#B98D4C',
                    foreground: '#16233A',
                },
                sidebar: {
                    DEFAULT: '#0F2042',
                    foreground: '#E8ECF3',
                    primary: '#B98D4C',
                    'primary-foreground': '#16233A',
                    accent: '#1F3350',
                    border: '#213456',
                },
            },
        },
    },
    plugins: [],
};
