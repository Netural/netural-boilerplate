var gulp = require('gulp');
var del = require('del');
var config = require('./config');

gulp.task('clean', function() {
    del.sync([config.dest.public]);
});
