const mix = require('laravel-mix');

mix
    .postCss('resources/assets/css/app.css',
        'public/assets/css/app.css')
    .js([
      'resources/assets/js/app.js',
    ], 'public/assets/js/app.js');
