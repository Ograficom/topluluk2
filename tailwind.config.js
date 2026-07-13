const defaultTheme = require('tailwindcss/defaultTheme');

const token = (name) => `oklch(from var(--${name}) l c h / <alpha-value>)`;

module.exports = {
    darkMode: 'class',
    content: [
        './app/**/*.php',
        './bootstrap/**/*.php',
        './config/**/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.jsx',
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
                primary: token('primary'),
                'primary-foreground': token('primary-foreground'),
                background: token('background'),
                foreground: token('foreground'),
                card: token('card'),
                'card-foreground': token('card-foreground'),
                popover: token('popover'),
                'popover-foreground': token('popover-foreground'),
                muted: token('muted'),
                'muted-foreground': token('muted-foreground'),
                accent: token('accent'),
                'accent-foreground': token('accent-foreground'),
                secondary: token('secondary'),
                'secondary-foreground': token('secondary-foreground'),
                border: token('border'),
                input: token('input'),
                ring: token('ring'),
                destructive: token('destructive'),
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
                DEFAULT: 'var(--radius)',
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
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
