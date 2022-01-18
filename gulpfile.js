const {src, dest, watch, series} = require('gulp');
const postcss = require('gulp-postcss');
const uglifycss = require('gulp-uglifycss');

function buildStyles() {
    const tailwindcss = require('tailwindcss');
    return src('./assets/*.css')
        .pipe(postcss([
            tailwindcss('./tailwind.config.js'),
        ]))
        .pipe(uglifycss())
        .pipe(dest('./www/assets/styles/'));
}

function watchFiles() {
    watch('./app/**/*.{html,js,latte}', buildStyles);
    console.log("Watching for Changes..\n");
}

exports.default = series(
    buildStyles,
    watchFiles,
);

