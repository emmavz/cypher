const mix = require('laravel-mix');
require('laravel-mix-clean');
const mixManifest = require('./public/mix-manifest');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Front
mix.js('resources/js/front/main.js', 'public/front/js');

if (!mix.inProduction()) {
    mix.sass('resources/css/front/styles.scss', 'public/front/css').options({
        processCssUrls: false
    });
} else {
    mix.sass('resources/css/front/styles.scss', 'public/front/css', {
        sassOptions: {
            outputStyle: 'expanded' // Not requird as it is default option
        }
    }, [
        require('postcss-import'),
        require('autoprefixer')( // Not required as mix automatically add autoprefixer
            // {grid: true}
        ),
    ]).options({
        processCssUrls: false
    });

    // File Hash Versioning
    mix.version();
    mix.then(() => {
        const convertToFileHash = require("laravel-mix-make-file-hash");
        convertToFileHash({
            publicPath: "public",
            manifestFilePath: "public/mix-manifest.json"
        });
    });
}

// Admin
mix.sass('resources/css/admin/admin.scss', 'public/admin_assets/dist/css').options({
    processCssUrls: false
});

mix.js('resources/js/admin/main.js', 'public/admin_assets/dist/js');



// Default laravel assets for auth
mix.js('resources/js/app.js', 'public/js').postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
]);



// For Browser Auto reload
mix.browserSync({
    open: false,
    proxy: 'http://localhost:8000',
    port: 8000,
    notify: false, // It will hide top right browserSync Banner,

});

// Disable success message in cmd during -watch
mix.disableSuccessNotifications();

// Clean old files
var oldFilesForClean = [];
for (x in mixManifest) oldFilesForClean.push('.' + mixManifest[x]);
mix.clean({
    cleanOnceBeforeBuildPatterns: oldFilesForClean,
});
