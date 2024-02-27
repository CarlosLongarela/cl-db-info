function getCurrentDate() {
	const date = new Date();
	const day = ( '0' + date.getDate() ).slice(-2);
	const month = ( '0' + ( date.getMonth() + 1 ) ).slice(-2);
	const year = date.getFullYear();

	return `${year}-${month}-${day}`;
}

const gulp       = require( 'gulp' ),
	sass         = require( 'gulp-sass' )( require( 'node-sass' ) ),
	autoprefixer = require( 'gulp-autoprefixer' ),
	plumber      = require( 'gulp-plumber' ),
	concat       = require( 'gulp-concat' ),
	log          = require( 'fancy-log' ),
	sourcemaps   = require( 'gulp-sourcemaps' ),
	ts           = require( 'gulp-typescript' ),
	rename       = require( 'gulp-rename' ),
	replace      = require( 'gulp-replace' ),
	uglify       = require( 'gulp-uglify' );

/** SCSS Task **/
gulp.task( 'admin-scss', function() {
	return gulp.src( './src/sass/general.scss' )
		.pipe( sourcemaps.init() )
		.pipe( sourcemaps.identityMap() )
		.pipe( plumber() )
		.pipe( concat( 'cl-db-info.css' ) )
		.pipe( replace( '//current-date//', `${getCurrentDate()}` ) )
		.pipe( autoprefixer( 'last 2 versions', '> 5%', 'not ie 6-9' ) )
		.pipe( sass( {outputStyle: 'compressed' } ).on( 'error', sass.logError ) )
		.pipe( sourcemaps.write( '../maps' ) )
		.pipe( gulp.dest( './assets' ) )
		.on( 'end', function() {
			log.info( 'Result Gulp Task CSS: Created: ./assets/cl-db-info.css' );
		} );
} );

/** TypeScript Task **/
gulp.task( 'admin-ts', function() {
	return gulp.src( './src/ts/general.ts' )
		.pipe( sourcemaps.init() )
		.pipe( sourcemaps.identityMap() )
		.pipe( plumber() )
		.pipe( concat( 'cl-db-info.ts' ) )
		.pipe( replace( '//current-date//', `${getCurrentDate()}` ) )
		.pipe( ts( {
			noImplicitAny: true,
			strict: true,
			allowJs: true,
			target: 'ES6',
			moduleResolution: 'node',
		} ) )
		.pipe( uglify( {
			output: {
				comments: 'some',
			},

		}) )
		.pipe( rename( { extname: '.js' } ) )
		.pipe( sourcemaps.write( '../maps' ) )
		.pipe( gulp.dest( './assets' ) )
		.on( 'end', function() {
			log.info( 'Result Gulp Task TS: Created: ./assets/cl-db-info.js' );
		} );
} );


gulp.task( 'watch', function() {
	// Inspect changes in all scss files.
	gulp.watch( [ './src/sass/**/*.scss' ], gulp.parallel( [ 'admin-scss' ] ) );
	// Inspect changes in all ts files.
	gulp.watch( [ './src/ts/**/*.ts' ], gulp.parallel( [ 'admin-ts' ] ) );
} );

gulp.task( 'default', gulp.parallel( 'watch', 'admin-scss', 'admin-ts' ) );
