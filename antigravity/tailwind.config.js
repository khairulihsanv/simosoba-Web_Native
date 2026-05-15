/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './lib/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      // Antigravity custom color palette — deep space meets clinical clarity
      colors: {
        brand: {
          50:  '#f0f4ff',
          100: '#e0eaff',
          200: '#c7d7fe',
          300: '#a5b8fc',
          400: '#8193f8',
          500: '#6366f1', // primary indigo
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81',
          950: '#1e1b4b',
        },
        surface: {
          DEFAULT: '#0f0f1a',
          card:    '#161628',
          border:  '#1e1e35',
          elevated:'#1c1c30',
        },
        accent: {
          green:  '#10b981',
          yellow: '#f59e0b',
          red:    '#ef4444',
          blue:   '#3b82f6',
          purple: '#8b5cf6',
        },
      },

      // Font family tokens
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Plus Jakarta Sans', 'sans-serif'],
        mono:  ['JetBrains Mono', 'monospace'],
      },

      // Custom border radius
      borderRadius: {
        '4xl': '2rem',
        '5xl': '2.5rem',
      },

      // Backdrop blur levels
      backdropBlur: {
        xs: '2px',
      },

      // Box shadow presets for glassmorphism cards
      boxShadow: {
        glass:   '0 8px 32px rgba(0, 0, 0, 0.37)',
        glow:    '0 0 40px rgba(99, 102, 241, 0.3)',
        'glow-sm':'0 0 20px rgba(99, 102, 241, 0.2)',
        card:    '0 4px 24px rgba(0, 0, 0, 0.4)',
      },

      // Animation keyframes
      keyframes: {
        'fade-in': {
          '0%':   { opacity: '0', transform: 'translateY(10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'slide-up': {
          '0%':   { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'pulse-glow': {
          '0%, 100%': { boxShadow: '0 0 20px rgba(99, 102, 241, 0.2)' },
          '50%':      { boxShadow: '0 0 40px rgba(99, 102, 241, 0.5)' },
        },
        'spin-slow': {
          from: { transform: 'rotate(0deg)' },
          to:   { transform: 'rotate(360deg)' },
        },
      },
      animation: {
        'fade-in':    'fade-in 0.5s ease forwards',
        'slide-up':   'slide-up 0.6s ease forwards',
        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
        'spin-slow':  'spin-slow 8s linear infinite',
      },
    },
  },
  plugins: [],
};
