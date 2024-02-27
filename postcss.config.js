module.exports = {
  plugins: [
    require('@csstools/postcss-sass')(),
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
  ],
};
