module.exports = function(grunt) {
  grunt.initConfig({
//    pkg: grunt.file.readJSON('package.json')
//  },
    
  watch: {
    compass: {
      files: ['../openeyes/**/*.{scss,sass}'],
      tasks: ['compass:dev']
    },
    js: {
      files: ['../openeyes/**/*.js'],
      tasks: ['uglify']
    }
  },
 
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
  
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('default', ['compass:dev', 'uglify', 'watch']);
};

