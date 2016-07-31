//npm install -g gulp
//npm install --save-dev gulp
var gulp         = require('gulp');
var environments = require('gulp-environments');
var gulpif       = require('gulp-if');
var uglify       = require('gulp-uglify');
var uglifycss    = require('gulp-uglifycss');
var sass         = require('gulp-sass');
var concat       = require('gulp-concat');
var sourcemaps   = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var del          = require('del');
var imagemin     = require('gulp-imagemin');

var development = environments.development;
var production  = environments.production;

environments.current(development);

var resourceDirectory = 'resource/';

var resourceViewsDirectory = 'resource/view/';

var destinationDir = 'web/';

var paths = {
  scriptsVendor: [
    'node_modules/jquery/dist/jquery.js',
    'node_modules/bootstrap/dist/js/bootstrap.js',
    'node_modules/jquery-ui-bundle/jquery-ui.js'
  ],

  scriptsCustom: [
    ////custom js
    resourceDirectory + 'js/**/*.js'
  ],
  stylesVendor:  [
    ////vendor
    'node_modules/bootstrap/dist/css/bootstrap.css',
    'node_modules/jquery-ui-bundle/jquery-ui.theme.css',
    'node_modules/font-awesome/css/font-awesome.css'
  ],

  stylesCustom: [
    ////custom styles
    resourceDirectory + 'css/**/*.css',
    resourceDirectory + 'sass/**/*.sass'
  ],

  imagesVendor: {
    jqueryUi: 'node_modules/jquery-ui-bundle/images/**.*'
  },

  imagesCustom:          [
    resourceDirectory + 'img/**/*'
  ],

  fonts: [
    'node_modules/font-awesome/fonts/**.*',
    'node_modules/bootstrap/fonts/**.*'
  ]
};

//JAVASCRIPT TASK: write one minified js file out of jquery.js, bootstrap.js and all of custom js files
gulp.task('jsVendor', function () {
  return gulp.src(paths.scriptsVendor)
    .pipe(production(sourcemaps.init()))
    .pipe(concat('vendor.js'))
    .pipe(production(uglify()))
    .pipe(production((sourcemaps.write('../maps'))))
    .pipe(gulp.dest(destinationDir + 'js'));
});

//JS custom
gulp.task('jsCustom', function () {
  return gulp.src(paths.scriptsCustom)
    .pipe(concat('custom.js'))
    .pipe(gulp.dest(destinationDir + 'js'));
});
//

gulp.task('cssConcat', function(){
  return gulp.src(paths.stylesCustom.concat(paths.stylesVendor))
  .pipe(uglifycss())
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9'))
    .pipe(concat('style.min.css'))
    .pipe(gulp.dest(resourceViewsDirectory + 'css'));
});

gulp.task('jsConcat', function(){
  return gulp.src(paths.scriptsVendor.concat(paths.scriptsCustom))
    .pipe(uglify())
    .pipe(concat('scripts.min.js'))
    .pipe(gulp.dest(destinationDir + 'js'));
});

//clean images folder
gulp.task('clean-jquery-ui-images-vendor', function () {
  return del(destinationDir + 'css/images/**/*');
});

//clean images folder
gulp.task('clean-images-custom', function () {
  return del(destinationDir + 'img/**/*');
});

//copy jquery-ui vendor image files
gulp.task('imagesVendorJqueryUi', ['clean-jquery-ui-images-vendor'], function () {
  return gulp.src(paths.imagesVendor.jqueryUi)
  //.pipe(imagemin({optimizationLevel: 5}))
    .pipe(gulp.dest(destinationDir + 'css/images'));
});

//clean, optimize and copy project image files
gulp.task('imagesCustom', ['clean-images-custom'], function () {
  return gulp.src(paths.imagesCustom)
    .pipe(imagemin({optimizationLevel: 5}))
    .pipe(gulp.dest(destinationDir + 'img'));
});

// Rerun the task when a file changes
gulp.task('watch', function () {
  gulp.watch(paths.scriptsVendor, ['jsVendor']);
  gulp.watch(paths.imagesVendor.jqueryUi, ['imagesVendorJqueryUi']);

  gulp.watch(paths.scriptsCustom, ['jsCustom']);
  gulp.watch(paths.imagesCustom, ['imagesCustom']);
});

//copy vendor fonts
gulp.task('fonts', function () {
  return gulp.src(paths.fonts)
    .pipe(gulp.dest(destinationDir + 'fonts'));
});

gulp.task('default', ['jsVendor', 'jsConcat', 'jsCustom', 'cssConcat',
                      'imagesVendorJqueryUi', 'imagesCustom', 'fonts']);
