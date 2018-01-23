import autoprefixer from 'autoprefixer';
import base64 from 'gulp-base64';
import browserSync from 'browser-sync';
import buffer from 'vinyl-buffer';
import concat from 'gulp-concat';
import cssnano from 'cssnano';
import del from 'del';
import File from 'vinyl';
import fs from 'fs';
import gulp from 'gulp';
import gulpif from 'gulp-if';
import gitrev from 'git-rev';
import imagemin from 'gulp-imagemin';
import notify from 'gulp-notify';
import plumber from 'gulp-plumber';
import php from 'gulp-connect-php';
import postCSS from 'gulp-postcss';
import rename from 'gulp-rename';
import sass from 'gulp-sass';
import sassGlob from 'gulp-sass-glob';
import stylelint from 'gulp-stylelint';
import source from 'vinyl-source-stream';
import sourcemaps from 'gulp-sourcemaps';
import size from 'gulp-size';
import webpack from 'webpack';
import webpackStream from 'webpack-stream';

import webpackConfig from './webpack.config.js';
import packageJSON from './package.json';

const reload = browserSync.reload;
const postcssPlugins = [
    autoprefixer({ browsers: packageJSON.browserslist }),
    cssnano()
];

const isProduction = process.env.NODE_ENV === 'production' ? true : false;

gulp.task('clean', function () {
    del.sync(['public']);
});

gulp.task('version', function () {
    return gitrev.short(function (str) {
            return string_src('version.cache', str).pipe(gulp.dest('public'))
        });
});

gulp.task('content', function () {
    return gulp.src(['app/**/*.{xml,json,yml,php}'])
        .pipe(gulp.dest('public'))
        .pipe(size({
            title: "content"
        }));
});

gulp.task('fonts', function () {
    return gulp.src('app/styles/fonts.scss')
        .pipe(base64({
            baseDir: __dirname,
            extensions: ['woff'],
            maxImageSize: 8000 * 1024,
            debug: true
        }))
        .pipe(rename('fonts.css'))
        .pipe(gulp.dest('public/styles'))
        .pipe(size({
            title: "fonts"
        }));
});

gulp.task('scripts', function () {
    return gulp.src('app/scripts/main.ts')
    .pipe(gulpif(!isProduction, plumber({errorHandler: notify.onError("JS: <%= error.message %>")})))
        .pipe(webpackStream(webpackConfig, webpack))
        .pipe(gulp.dest('public/scripts'))
        .pipe(browserSync.stream());
});

gulp.task('styles', function () {
    return gulp.src(['app/styles/**/*.{sass,scss}', '!app/styles/fonts.scss'])
        .pipe(plumber({errorHandler: notify.onError("SCSS Error: <%= error.message %>")}))
        .pipe(sassGlob())
        .pipe(stylelint({
          reporters: [
            {formatter: 'string', console: true}
          ]
        }))
        .on('error', error => console.error(error.message))
        .pipe(sourcemaps.init())
        .pipe(gulpif(!isProduction, sourcemaps.init()))
        .pipe(sass({
            outputStyle: 'expanded'
        }))
        .pipe(postCSS(postcssPlugins))
        .pipe(gulpif(!isProduction, sourcemaps.write('.')))
        .pipe(gulp.dest('public/styles'))
        .pipe(browserSync.stream())
        .pipe(size({
            title: "styles"
        }));
});

gulp.task('images', function () {
    return gulp.src('app/images/**/*.{jpg,jpeg,png,gif,svg,ico}')
        .pipe(imagemin({
            progressive: true,
            interlaced: true
        }))
        .pipe(gulp.dest('public/images'))
        .pipe(size({
            title: "images"
        }));
});

gulp.task('php', function () {
    php.server({
        hostname: '0.0.0.0',
        base: 'public',
        port: 4040,
        open: false
    });
});

gulp.task('default', ['clean', 'version', 'styles', 'scripts', 'fonts', 'images', 'content', 'php'], function () {
    browserSync({
        port: 4200,
        proxy: {
            target: 'http://localhost:4040',
            reqHeaders: function (config) {
                return {
                    'accept-encoding': 'identity',
                    'agent': false
                }
            }
        },
        reloadDelay: 1000,
        notify: true,
        open: true,
        logLevel: 'silent'
    });

    gulp.watch('webapp/views/**/*', reload);
    gulp.watch('app/styles/**/*', ['styles']);
    gulp.watch('app/scripts/**/*', ['scripts']);
});

gulp.task('build', ['clean', 'version', 'styles', 'scripts', 'fonts', 'images', 'content']);

function string_src(filename, string) {
    var src = require('stream').Readable({ objectMode: true });
    src._read = function () {
        this.push(new File({ cwd: "", base: ".", path: filename, contents: new Buffer(string) }))
        this.push(null)
    };
    return src;
}
