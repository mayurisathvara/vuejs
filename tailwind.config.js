/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#fff5e6',
          100: '#ffe6cc',
          200: '#ffcc99',
          300: '#ffb366',
          400: '#ff9933',
          500: '#ff6b00',
          600: '#ff8c33',
          700: '#cc5500',
          800: '#994000',
          900: '#662b00',
        },
        orange: {
          primary: '#ff6b00',
          secondary: '#ff8c33',
          light: '#ffb366',
          lighter: '#ffd699',
          dark: '#cc5500',
          darker: '#994000',
        },
      },
    },
  },
  plugins: [],
}
