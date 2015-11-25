module.exports = function(grunt) {
	grunt.initConfig({
 
	compass: {
		dev: {
			options: {              
				sassDir: '../openeyes/protected/assets/sass',
				cssDir: '../openeyes/protected/assets/css',
				imagesDir: '../openeyes/protected/assets/img',
				generatedImagesDir: '../openeyes/protected/assets/img/sprites',
				outputStyle: 'expanded',
				relativeAssets: true,
				httpPath: '',
				noLineComments: false,
				importPath: [
					'../openeyes/protected/assets/components/foundation/scss/',
					'/var/www/openeyes/protected/assets/components/foundation/scss/'
				]
			}
		},
	}}),
   

	grunt.registerTask('buildmodule', function(dir) {
		var done = this.async();

		grunt.log.writeln('Processing ' + dir);

		grunt.util.spawn({
			grunt: true,
			args:['compass'],
			opts: {
				cwd: dir
			}
		},

		function(err, result, code) {
			if (err == null) {
				grunt.log.writeln('processed ' + dir);
				done();
			}
			else {
				grunt.log.writeln('module ' + dir + ' not found: ' + code);
				done();	// false for failure/halt
			}
		})
	});

	grunt.registerTask('buildmodules', function() {
		grunt.task.run(['buildmodule:../openeyes/protected/modules/OphCiExamination']);
		grunt.task.run(['buildmodule:../openeyes/protected/modules/OphCiPhasing']);
	});


	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-uglify');

	grunt.registerTask('default', ['compass:dev', 'uglify', 'buildmodules']);


};

