module.exports = (ctx) => ({
  plugins: [
    require('@csstools/postcss-sass')(),
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
    ctx.env === 'production' && require('cssnano')({ preset: 'default' }),
  ],
});
