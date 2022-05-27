const mix = require('laravel-mix');
const path = require('path');
const webpack = require('webpack');

mix.disableNotifications();

if (mix.inProduction()) {
    mix
        .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
        .js('resources/js/start/main.js', 'public/js/start/main.js')
        .version()
    ;

    mix
        .webpackConfig({
            resolve: {
                alias: {
                    "@": path.resolve(__dirname, "resources/js/midone")
                }
            },
            plugins: [
                new webpack.DefinePlugin({
                    __VUE_OPTIONS_API__: true,
                    __VUE_PROD_DEVTOOLS__: false,
                    __VUE_I18N_FULL_INSTALL__: true,
                    __VUE_I18N_LEGACY_API__ : true,
                    __INTLIFY_PROD_DEVTOOLS__ : false
                })
            ]
        })
        .postCss('resources/css/midone/app.css', 'public/css/midone', [
            require('postcss-import'),
            require('postcss-advanced-variables'),
            require('tailwindcss/nesting'),
            require('tailwindcss')('./tailwind.config.js'),
            require('autoprefixer'),
            require('postcss-url'),
            require('cssnano')
        ])
        .js('resources/js/midone/main.js','public/js/midone/main.js')
        .vue({ version: 3 })
        .version()
    ;

} else {
    mix
        .webpackConfig({
            stats: {
                children: false
            },
            devtool: 'source-map'
        })
        .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
        .js('resources/js/start/main.js', 'public/js/start/main.js')
        .sourceMaps()
    ;

    mix
        .webpackConfig({
            stats: {
                children: false
            },
            devtool: 'source-map',
            resolve: {
                alias: {
                    "@": path.resolve(__dirname, "resources/js/midone/")
                }
            },
            plugins: [
                new webpack.DefinePlugin({
                    __VUE_OPTIONS_API__: true,
                    __VUE_PROD_DEVTOOLS__: false,
                    __VUE_I18N_FULL_INSTALL__: true,
                    __VUE_I18N_LEGACY_API__ : true,
                    __INTLIFY_PROD_DEVTOOLS__ : false
                })
            ]
        })
        .postCss('resources/css/midone/app.css', 'public/css/midone', [
            require('postcss-import'),
            require('postcss-advanced-variables'),
            require('tailwindcss/nesting'),
            require('tailwindcss')('./tailwind.config.js'),
            require('autoprefixer'),
            require('postcss-url')
        ])
        .js('resources/js/midone/main.js','public/js/midone/main.js')
        .sourceMaps()
        .vue({ version: 3 })
    ;
}

