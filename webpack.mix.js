const mix = require('laravel-mix');

mix.setPublicPath('dist')
    .js('resources/js/cards.js', 'js')
    .vue();
