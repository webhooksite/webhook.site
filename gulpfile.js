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
    mix.sass('app.scss');
    mix.browserify([
        // external dependencies
        'jquery-2.2.2.min.js',
        'angular.min.js',
        'angular-ui-router.js',
        'autotrack.js',
        'bootstrap.min.js',
        'bootstrap-notify.min.js',
        'clipboard.min.js',

        // application code
        'echo.js',
        'app.js',
    ]);
});
