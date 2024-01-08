const mix = require('laravel-mix');

mix
    .sass('resources/assets/sass/app.scss',
        'public/assets/css/app.css')
    .js([
      'resources/assets/js/app.js',
    ], 'public/assets/js/app.js');
