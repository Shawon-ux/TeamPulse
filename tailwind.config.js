import forms from '@tailwindcss/forms';
const colors = ['indigo','emerald','rose','amber','sky','violet'];
export default {
  darkMode: 'class',
  content: ['./resources/**/*.blade.php', './resources/**/*.js', './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php'],
  safelist: colors.flatMap(c => [`bg-${c}-500`, `bg-${c}-50`, `bg-${c}-100`, `text-${c}-600`, `text-${c}-800`, `text-${c}-900`, `border-${c}-200`]),
  theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } },
  plugins: [forms],
};
