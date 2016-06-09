var gulp = require('gulp');
var ftp = require('vinyl-ftp');
var gutil = require('gulp-util');
var fs = require('fs');
if (fs.existsSync('../.ftppass.json')) {
    var ftppass = require('../.ftppass.json');
}
gulp.task('sftp:dev', function() {
    if (ftppass) {
        var conn = ftp.create({
            host: ftppass.dev.server,
            user: ftppass.dev.user,
            password: ftppass.dev.pass,
            parallel: 4,
            log: gutil.log
        });

        var globs = [
            'public/**',
            'webapp/views/**',
        ];

        // using base = '.' will transfer everything to /public_html correctly 
        // turn off buffering in gulp.src for best performance 

        return gulp.src(globs, {
                base: '.',
                buffer: false
            })
            .pipe(conn.newer('/htdocs')) // only upload newer files 
            .pipe(conn.dest('/htdocs'));
    } else {
		console.log('For direct upload via sftp you will need a .ftppass.json');
	}
});