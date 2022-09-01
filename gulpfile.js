'use strict'

var gulp = require('gulp')
var jshint = require('gulp-jshint')
var stylish = require('jshint-stylish')
var rename = require('gulp-rename')
var concat = require('gulp-concat')
var uglify = require('gulp-uglify')
var cleanCss = require('gulp-clean-css')
var notify = require('gulp-notify')
var sourcemaps = require('gulp-sourcemaps')
var glob = require('glob')
var gutil = require('gulp-util')
var babel = require("gulp-babel")
var plumber = require("gulp-plumber")
var sass = require('gulp-sass')
sass.compiler = require('node-sass')

gulp.task('js', function () {
  glob('./src/js/*.js', function (err, files) {
    if (err) {
      done(err)
    }

    var tasks = files.map(function (entry) {
      return gulp.src(entry)
        .pipe(plumber())
        .pipe(
          babel({
            presets: [
              [
                "@babel/env",
                {
                  modules: false
                }
              ]
            ]
          })
        )
        .pipe(uglify().on('error', gutil.log))
        .pipe(rename(function (path) {
          path.extname = '.min.js'
        }))
        .pipe(gulp.dest('./assets/js/'))
        .pipe(notify({ message: 'Finished minifying ' + entry }))
    })
  })
})

gulp.task('listing-template-js', function () {
  glob('./src/js/listing-templates/*.js', function (err, files) {
    if (err) {
      done(err)
    }

    var tasks = files.map(function (entry) {
      return gulp.src(entry)
        .pipe(plumber())
        .pipe(
          babel({
            presets: [
              [
                "@babel/env",
                {
                  modules: false
                }
              ]
            ]
          })
        )
        .pipe(uglify().on('error', gutil.log))
        .pipe(rename(function (path) {
          path.extname = '.min.js'
        }))
        .pipe(gulp.dest('./assets/js/listing-templates/'))
        .pipe(notify({ message: 'Finished minifying ' + entry }))
    })
  })
})

gulp.task('sass', function () {
  glob('./src/scss/*.scss', function (err, files) {
    if (err) {
      done(err)
    }

    var tasks = files.map(function (entry) {
      return gulp.src(entry)
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCss())
        .pipe(rename(function (path) {
          path.extname = '.min.css'
        }))
        .pipe(gulp.dest('./assets/css/'))
        .pipe(notify({ message: 'Finished processing ' + entry }))
    })
  })
});

gulp.task('css', function () {
  glob('./src/css/*.css', function (err, files) {
    if (err) {
      done(err)
    }

    var tasks = files.map(function (entry) {
      return gulp.src(entry)
        .pipe(cleanCss())
        .pipe(rename(function (path) {
          path.extname = '.min.css'
        }))
        .pipe(gulp.dest('./assets/css/'))
        .pipe(notify({ message: 'Finished processing ' + entry }))
    })
  })
})

gulp.task('widget-css', function () {
  glob('./src/css/widgets/*.css', function (err, files) {
    if (err) {
      done(err)
    }

    var tasks = files.map(function (entry) {
      return gulp.src(entry)
        .pipe(cleanCss())
        .pipe(rename(function (path) {
          path.extname = '.min.css'
        }))
        .pipe(gulp.dest('./assets/css/widgets/'))
        .pipe(notify({ message: 'Finished processing ' + entry }))
    })
  })
})

gulp.task('listing-template-css', function () {
  glob('./src/css/listing-templates/*.css', function (err, files) {
    if (err) {
      done(err)
    }

    var tasks = files.map(function (entry) {
      return gulp.src(entry)
        .pipe(cleanCss())
        .pipe(rename(function (path) {
          path.extname = '.min.css'
        }))
        .pipe(gulp.dest('./assets/css/listing-templates/'))
        .pipe(notify({ message: 'Finished processing ' + entry }))
    })
  })
})

gulp.task('readme', function () {
  // convert readme.txt to readme.md
  console.log('readme conversion not yet implemented')
})

gulp.task('watch', function () {
  gulp.watch('./src/js/*.js', ['js'])
  gulp.watch('./src/js/listing-templates/*.js', ['listing-template-js'])
  gulp.watch('./src/scss/*.scss', ['sass'])
  gulp.watch('./src/css/*.css', ['css'])
  gulp.watch('./src/css/widgets/*.css', ['widget-css'])
  gulp.watch('./src/css/listing-templates/*.css', ['listing-template-css'])
})

gulp.task('default', ['js'], function () {})
