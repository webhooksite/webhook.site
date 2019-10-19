var elixir = require('laravel-elixir');
require("laravel-elixir-webpack");

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.copy('node_modules/bootstrap-sass/assets/fonts/bootstrap', 'public/fonts/bootstrap');
    mix.copy('node_modules/socket.io-client/dist/socket.io.js*', 'public/js');
    mix.sass('app.scss');
    mix.browserify(['libs.js'], 'public/js/libs.js');
    mix.browserify(['echo.js', 'app.js']);
});
