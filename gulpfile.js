'use strict'

var gulp = require('gulp')
var jshint = require('gulp-jshint')
var stylish = require('jshint-stylish')
var rename = require('gulp-rename')
var concat = require('gulp-concat')
var uglify = require('gulp-uglify')
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

gulp.task('sass', function () {
  glob('./src/scss/*.scss', function (err, files) {
    if (err) {
      done(err)
    }

    var tasks = files.map(function (entry) {
      return gulp.src(entry)
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./assets/css/'))
        .pipe(notify({ message: 'Finished processing ' + entry }))
    })
  })
});

gulp.task('css', function () {
  // concat CSS and put them in appropriate space
  // will implement once we implement Sass
  console.log('css task not implemented yet')
})

gulp.task('readme', function () {
  // convert readme.txt to readme.md
  console.log('readme conversion not yet implemented')
})

gulp.task('watch', function () {
  gulp.watch('./src/js/*.js', ['js'])
  gulp.watch('./src/scss/*.scss', ['sass'])
})

gulp.task('default', ['js'], function () {})
