const mix = require('laravel-mix')

// Для маски - @import "blocks/**/*.scss"
mix.webpackConfig({module: {rules: [{test: /\.scss$/, loader: 'import-glob-loader'},]}});

// JS
mix.js('resources/js/app.js', 'public/assets/js').autoload({
    //jquery: ['$', 'window.jQuery', 'jQuery']
})

    // SASS
    .sass('resources/sass/app.scss', 'public/assets/css/')

    .options({
        processCssUrls: false
    })

    .version();
