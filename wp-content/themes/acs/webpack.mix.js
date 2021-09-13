const mix = require('laravel-mix');

mix.setPublicPath('public');

mix.sass('resources/scss/app.scss', 'public/css').sourceMaps();
mix.js('resources/js/app.js', 'public/js');

mix.browserSync({
    proxy: 'localhost'
})
