// tailwind.config.js
module.exports = {
  content: [
    './pages/**/*.php',
    './includes/**/*.php',
    './components/**/*.php',
    './*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        serif: ['ui-serif', 'Georgia', 'Cambria', '"Times New Roman"', 'Times', 'serif'],
        formal: ['Georgia', 'Cambria', 'Times', 'serif'],
        sans: ['Segoe UI', 'Arial', 'Helvetica', 'sans-serif'],
      },
    },
  },
  plugins: [],
}