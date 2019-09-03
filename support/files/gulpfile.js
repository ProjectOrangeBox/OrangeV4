const { series, parallel, src, dest, watch } = require('gulp');
const sass = require('gulp-sass');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const pug = require('gulp-pug');
const concat = require('gulp-concat');
const cleanCSS = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const del = require('del');

/* attach the sass compiler to the sass class */
sass.compiler = require('node-sass');

const tempFolder = 'var/gulp';
const distFolder = 'public/dist';

let pugViews = [
];

let copyDir = {
	'node_modules/font-awesome/fonts/*': 'public/fonts',
	'node_modules/roboto-fontface/fonts/roboto/*': 'public/fonts/roboto',
	'node_modules/bootstrap3/fonts/*': 'public/fonts',
};

let js = {
	'vendor': [
		'node_modules/jquery/dist/jquery.js',
		'node_modules/bootstrap3/dist/js/bootstrap.js',
		'node_modules/jstorage/jstorage.js',
		'node_modules/handlebars/dist/handlebars.js',
	],
	'user': [
		'assets/application.js'
	],
};

let css = {
	'vendor': [
		'node_modules/bootstrap3/dist/css/bootstrap.css',
		'node_modules/roboto-fontface/css/roboto/roboto-fontface.css',
		'node_modules/font-awesome/css/font-awesome.css',
	],
	'user': [
		'assets/application.css'
	],
	'scss': [],
};

/* all config finished */

/* auto build the watch arrays */
let watchFiles = Array.prototype.concat(pugViews,css.user,css.vendor,css.scss,js.vendor,js.user);
let watchFilesJs = Array.prototype.concat(js.user);
let watchFilesCss = Array.prototype.concat(css.user,css.vendor,css.scss);
let watchFilesPug = pugViews;

var tasks = {
	compilePug: function(cb) {
		return (pugViews.length)
			? src(pugViews)
				.pipe(pug({pretty:false}))
				.pipe(dest('application/views'))
			: cb();
	},
	compileJsVendor: function() {
		return src(js.vendor)
			.pipe(concat('1_vendor.js'))
			.pipe(dest(tempFolder));
	},
	compileJsUser: function() {
		/*
		.pipe(sourcemaps.init())
		.pipe(sourcemaps.write('.'))
		*/

		return src(js.user)
			.pipe(uglify())
			.pipe(concat('2_user.js'))
			.pipe(dest(tempFolder));
	},
	combinedJs: function() {
		return src(tempFolder + '/*.js')
			.pipe(concat('bundle.js'))
			.pipe(dest(distFolder));
	},
	compileSass: function(cb) {
		return (css.scss.length)
			? src(css.scss)
				.pipe(sass())
				.pipe(concat('2_sass.css'))
				.pipe(dest(tempFolder))
			: cb();
	},
	compileCss: function() {
		return src(css.vendor)
			.pipe(src(css.user))
			.pipe(concat('1_css.css'))
			.pipe(dest(tempFolder));
	},
	combinedCss: function() {
		return src(tempFolder + '/*.css')
			.pipe(cleanCSS({compatibility: 'ie9'}))
			.pipe(concat('bundle.css'))
			.pipe(dest(distFolder));
	},
	copyDirectories: function() {
		for (let idx in copyDir) {
			var callback = src(idx).pipe(dest(copyDir[idx]));
		};

		return callback;
	},
	cleanUp: function(cb) {
		return del([tempFolder + '/*',distFolder + '/*'],cb);
	}
};

exports.watch = ()=>{
	exports.default();
	watch(watchFiles,parallel(series(tasks.compileJsUser,tasks.combinedJs),series(tasks.compileSass,tasks.compileCss,tasks.combinedCss),tasks.compilePug));
}

exports['watch:js'] = ()=>{
	exports.default();
	watch(watchFilesJs,series(tasks.compileJsUser,tasks.combinedJs));
}

exports['watch:css'] = ()=>{
	exports.default();
	watch(watchFilesCss,series(parallel(tasks.compileSass,tasks.compileCss),tasks.combinedCss));
}

exports['watch:pug'] = ()=>{
	exports.default();
	watch(watchFilesPug,parallel(tasks.compilePug));
}

exports.clean = series(tasks.cleanUp);

exports.default = series(
	tasks.cleanUp,
	parallel(
		tasks.copyDirectories,
		series(parallel(tasks.compileJsVendor,tasks.compileJsUser),tasks.combinedJs),
		series(parallel(tasks.compileSass,tasks.compileCss),tasks.combinedCss),
		tasks.compilePug,
	)
);
