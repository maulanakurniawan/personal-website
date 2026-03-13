/** @type {import('tailwindcss').Config} */
import plugin from 'tailwindcss/plugin';

export default {
  content: [
    './resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('daisyui'),
    plugin(function ({ addBase, addComponents }) {
      addBase({
        "[data-theme='solohours'], [data-theme='solohours-dark']": {
          fontFamily: 'ui-sans-serif, system-ui, sans-serif',
        },
        "[data-theme='solohours'] h1, [data-theme='solohours-dark'] h1": {
          fontSize: '1.875rem',
          lineHeight: '2.25rem',
          fontWeight: '700',
        },
        "[data-theme='solohours'] h2, [data-theme='solohours-dark'] h2": {
          fontSize: '1.5rem',
          lineHeight: '2rem',
          fontWeight: '600',
        },
        "[data-theme='solohours'] h3, [data-theme='solohours-dark'] h3": {
          fontSize: '1.25rem',
          lineHeight: '1.75rem',
          fontWeight: '600',
        },
        "[data-theme='solohours'] h4, [data-theme='solohours-dark'] h4": {
          fontSize: '1.125rem',
          lineHeight: '1.75rem',
          fontWeight: '600',
        },
        "[data-theme='solohours'] h5, [data-theme='solohours-dark'] h5": {
          fontSize: '1rem',
          lineHeight: '1.5rem',
          fontWeight: '600',
        },
        "[data-theme='solohours'] h6, [data-theme='solohours-dark'] h6": {
          fontSize: '0.875rem',
          lineHeight: '1.25rem',
          fontWeight: '600',
          textTransform: 'uppercase',
          letterSpacing: '0.025em',
        },
        "[data-theme='solohours'] h1, [data-theme='solohours'] h2, [data-theme='solohours'] h3, [data-theme='solohours'] h4, [data-theme='solohours'] h5, [data-theme='solohours'] h6": {
          color: '#000000',
        },
        "[data-theme='solohours-dark'] h1, [data-theme='solohours-dark'] h2, [data-theme='solohours-dark'] h3, [data-theme='solohours-dark'] h4, [data-theme='solohours-dark'] h5, [data-theme='solohours-dark'] h6": {
          color: '#ffffff',
        },
        'button, [role=\"button\"]': {
          minHeight: '2.5rem',
          minWidth: '2.5rem',
          display: 'inline-flex',
          alignItems: 'center',
        },
        'button, [role=\"button\"]': {
          justifyContent: 'center',
        },
        'input:focus, input:focus-visible, select:focus, select:focus-visible, textarea:focus, textarea:focus-visible, .input:focus, .input:focus-visible, .select:focus, .select:focus-visible, .textarea:focus, .textarea:focus-visible': {
          outline: 'none !important',
          boxShadow: 'none !important',
        },
        '@media (min-width: 768px)': {
          "[data-theme='solohours'] h1, [data-theme='solohours-dark'] h1": {
            fontSize: '2.25rem',
            lineHeight: '2.5rem',
          },
        },
      });

      addComponents({
        '.btn, a[role="button"]': {
          '@apply btn-sm': {},
        },
        '.input': {
          '@apply input-sm': {},
        },
        '.select': {
          '@apply select-sm': {},
        },
        '.textarea': {
          '@apply textarea-sm': {},
        },
        '.checkbox': {
          '@apply checkbox-sm': {},
        },
        '.radio': {
          '@apply radio-sm': {},
        },
        '.toggle': {
          '@apply toggle-sm': {},
        },
        '.file-input': {
          '@apply file-input-sm': {},
        },
        "[data-theme='solohours'] .btn:not(.btn-primary):not(.btn-error):not(.btn-success):not(.btn-warning):not(.btn-info):not(.btn-accent):not(.btn-ghost)": {
          '@apply border-slate-300 bg-slate-100 text-black': {},
        },
        "[data-theme='solohours'] .btn:not(.btn-primary):not(.btn-error):not(.btn-success):not(.btn-warning):not(.btn-info):not(.btn-accent):not(.btn-ghost):hover": {
          '@apply border-slate-400 bg-slate-200': {},
        },
        "[data-theme='solohours-dark'] .btn:not(.btn-primary):not(.btn-error):not(.btn-success):not(.btn-warning):not(.btn-info):not(.btn-accent):not(.btn-ghost)": {
          '@apply border-slate-700 bg-black text-white': {},
        },
        "[data-theme='solohours-dark'] .btn:not(.btn-primary):not(.btn-error):not(.btn-success):not(.btn-warning):not(.btn-info):not(.btn-accent):not(.btn-ghost):hover": {
          '@apply border-slate-600 bg-slate-900': {},
        },
        "[data-theme='solohours'] .btn-error, [data-theme='solohours-dark'] .btn-error": {
          '@apply border-red-500 bg-red-500 text-white': {},
        },
        "[data-theme='solohours'] .btn-error:hover, [data-theme='solohours-dark'] .btn-error:hover": {
          '@apply border-red-600 bg-red-600 text-white': {},
        },
        "[data-theme='solohours'] .btn-ghost, [data-theme='solohours'] .btn-ghost:hover, [data-theme='solohours'] .btn-ghost:focus-visible, [data-theme='solohours'] .btn-ghost:active": {
          borderWidth: '0',
          borderColor: 'transparent',
          backgroundColor: 'transparent',
          boxShadow: 'none',
        },
        "[data-theme='solohours-dark'] .btn-ghost, [data-theme='solohours-dark'] .btn-ghost:hover, [data-theme='solohours-dark'] .btn-ghost:focus-visible, [data-theme='solohours-dark'] .btn-ghost:active": {
          borderWidth: '0',
          borderColor: 'transparent',
          backgroundColor: 'transparent',
          boxShadow: 'none',
        },
        "[data-theme='solohours'] .card, [data-theme='solohours-dark'] .card": {
          '@apply bg-base-100 border border-base-200 shadow': {},
        },
        "[data-theme='solohours-dark'] .card": {
          '@apply bg-base-200 shadow-black/40': {},
        },
        "[data-theme='solohours'] .card-highlight": {
          '@apply bg-emerald-50 border-emerald-200': {},
        },
        "[data-theme='solohours-dark'] .card-highlight": {
          '@apply bg-emerald-900/50 border-emerald-700': {},
        },
        "[data-theme='solohours'] .card-feature, [data-theme='solohours-dark'] .card-feature": {
          '@apply shadow-xl': {},
        },
        "[data-theme='solohours'] .table, [data-theme='solohours-dark'] .table": {
          '@apply w-full': {},
        },
        "[data-theme='solohours'] .table :where(thead, tfoot), [data-theme='solohours-dark'] .table :where(thead, tfoot)": {
          '@apply text-base-content': {},
        },
        "[data-theme='solohours'] .table :where(th, td), [data-theme='solohours-dark'] .table :where(th, td)": {
          '@apply border-base-200': {},
        },
      });
    }),
  ],
  daisyui: {
    themes: [
      {
        solohours: {
          primary: '#2563EB',
          secondary: '#f59e0b',
          error: '#ef4444',
          'error-content': '#ffffff',
          neutral: '#0f172a',
          'base-100': '#ffffff',
          'base-200': '#f1f5f9',
          'base-content': '#000000',
        },
      },
      {
        'solohours-dark': {
          primary: '#2563EB',
          secondary: '#f59e0b',
          error: '#ef4444',
          'error-content': '#ffffff',
          neutral: '#0f172a',
          'base-100': '#0b1120',
          'base-200': '#111827',
          'base-content': '#ffffff',
        },
      },
    ],
  },
};
