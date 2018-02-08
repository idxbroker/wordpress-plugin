'use strict';

var gulp = require('gulp'),
        jshint = require('gulp-jshint'),
        stylish = require('jshint-stylish'),
        rename = require('gulp-rename'),
        concat = require('gulp-concat'),
        uglify = require('gulp-uglify'),
        notify = require('gulp-notify'),
        sourcemaps = require('gulp-sourcemaps'),
        glob = require('glob'),
        gutil = require('gulp-util');

/*
gulp.task('jshint', function(){

    return gulp.src('./assets/src/js/*.js')
        .pipe ( jshint( ) )
        .pipe ( jshint.reporter( stylish ) )
        .pipe ( jshint.reporter( 'fail' ) );
})
*/

gulp.task('js', function() {

    glob('./assets/src/js/*.js', function(err, files) {
        if (err) done(err);

        var tasks = files.map(function(entry) {
            return gulp.src(entry)
                .pipe(uglify().on('error', gutil.log))
                .pipe(rename(function (path) {
                    path.extname = '.min.js';
                    }))
                .pipe(gulp.dest('./assets/js/'))
                .pipe(notify({message: 'Finished minifying ' + entry }));
            })
        })

})

gulp.task('css', function() {
    //concat CSS and put them in appropriate space
    // will implement once we implement Sass
    console.log('css task not implemented yet');
})

gulp.task('readme', function() {
    //convert readme.txt to readme.md
    console.log('readme conversion not yet implemented');
})


gulp.task('watch', function() {
    gulp.watch('./assets/src/js/*.js', ['js']);
})

gulp.task('default', ['js'], function() {});
