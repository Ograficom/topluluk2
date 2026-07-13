const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    darkMode: 'class',
    content: [
        './app/**/*.php',
        './bootstrap/**/*.php',
        './config/**/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './routes/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                display: ['Roboto', 'sans-serif'],
                body: ['Roboto', 'sans-serif'],
                sans: ['Roboto', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                sitebg: '#f4f4f5',
                primary: 'hsl(var(--primary) / <alpha-value>)',
                background: 'hsl(var(--background) / <alpha-value>)',
                foreground: 'hsl(var(--foreground) / <alpha-value>)',
                muted: 'hsl(var(--muted) / <alpha-value>)',
                'muted-foreground': 'hsl(var(--muted-foreground) / <alpha-value>)',
                secondary: 'hsl(var(--secondary) / <alpha-value>)',
                border: 'hsl(var(--border) / <alpha-value>)',
                destructive: 'hsl(var(--destructive) / <alpha-value>)',
                deeper: 'hsl(var(--deeper) / <alpha-value>)',
                endpoint: 'hsl(var(--endpoint) / <alpha-value>)',
                'primary-dark': '#27272a',
                'background-light': '#f4f4f5',
                'background-dark': '#09090b',
                'surface-light': '#ffffff',
                'surface-dark': '#18181b',
                'header-bg': '#f4f4f5',
                'header-bg-dark': '#18181b',
                'card-light': '#ffffff',
                'card-dark': '#18181b',
                'text-light': '#18181b',
                'text-dark': '#fafafa',
                'muted-light': '#71717a',
                'muted-dark': '#a1a1aa',
                'text-main-light': '#18181b',
                'text-main-dark': '#fafafa',
                'text-sub-light': '#71717a',
                'text-sub-dark': '#a1a1aa',
            },
            borderRadius: {
                DEFAULT: '0.5rem',
                full: '9999px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        function ({ addUtilities }) {
            addUtilities({
                '.scrollbar-thin': {
                    'scrollbar-width': 'thin',
                },
                '.scrollbar-track-transparent': {
                    'scrollbar-color': 'auto transparent',
                },
                '.scrollbar-thumb-border': {
                    'scrollbar-color': 'hsl(var(--border)) transparent',
                },
            });
        },
    ],
};
