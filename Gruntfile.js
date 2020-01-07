module.exports = function (grunt) {

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');

  grunt.initConfig({
    
    clean: {
      dist: [
        'lib/dev/css',
        'lib/dist',
      ]
    },

    concat : {

      'defaults-main' : {
        src: [
          'lib/dev/concat-lib/src/defaults/moment.js',
          'lib/dev/concat-lib/src/defaults/jsencrypt.js',
        
        ],
        dest: 'lib/dev/concat-lib/dist/defaults-main.js'
      },

      'defaults-admin' : {
        src: [
          'lib/dev/concat-lib/src/defaults/moment.js',
          'lib/dev/concat-lib/src/defaults/bootstrap.js',
          'lib/dev/concat-lib/src/defaults/jsencrypt.js',
          'lib/dev/concat-lib/src/defaults/summernote.js',
          'lib/dev/concat-lib/src/defaults/sortable.js',
          'lib/dev/concat-lib/src/defaults/sortable-jquery.js',

          'lib/dev/concat-lib/src/defaults/codemirror.js',
          'lib/dev/concat-lib/src/defaults/codemirror-addons/mode/javascript.js',
          'lib/dev/concat-lib/src/defaults/codemirror-addons/mode/css.js',

          'lib/dev/concat-lib/src/defaults/codemirror-addons/matchbrackets.js',
          // 'lib/dev/concat-lib/src/defaults/codemirror-addons/comment.js',
          'lib/dev/concat-lib/src/defaults/codemirror-addons/continuecomment.js',
          'lib/dev/concat-lib/src/defaults/codemirror-addons/closebrackets.js',
          'lib/dev/concat-lib/src/defaults/codemirror-addons/active-line.js',
          
          'lib/dev/concat-lib/src/bootstrap/bootstrap-validator.js',
          'lib/dev/concat-lib/src/bootstrap/bootstrap-select.js',
          'lib/dev/concat-lib/src/bootstrap/bootstrap-datetimepicker.js',
          'lib/dev/concat-lib/src/bootstrap/bootstrap-colorpicker.js',

        ],
        dest: 'lib/dev/concat-lib/dist/defaults-admin.js'
      },

      'jquery-default-plugins-main' : {
        src: [

          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.cookie.js',
          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.blockUI.js',

        ],
        dest: 'lib/dev/concat-lib/dist/jquery-default-plugins-main.js'
      },
      
  
      'jquery-default-plugins-admin' : {
        src: [

          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.cookie.js',
          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.blockUI.js',
          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.ui.js',

          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.iframe-transport.js',

          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.fileupload/jquery.fileupload.js',
          'lib/dev/concat-lib/src/jquery-default-plugins/jquery.fileupload/jquery.fileupload-process.js',
          

        ],
        dest: 'lib/dev/concat-lib/dist/jquery-default-plugins-admin.js'
      },

      'pb-main' : {
        src: [
          'lib/dev/concat-lib/src/pb/modules/pb.mainmodule.js',
          'lib/dev/concat-lib/src/pb/modules/pb.crypt.js',
          'lib/dev/concat-lib/src/pb/modules/pb.utils.js',
          'lib/dev/concat-lib/src/pb/modules/pb.lang.js',

        ],
        dest: 'lib/dev/concat-lib/dist/pb-main.js'
      },

      'pb-admin' : {
        src: [
          'lib/dev/concat-lib/src/pb/modules/pb.mainmodule.js',
          'lib/dev/concat-lib/src/pb/modules/pb.crypt.js',
          'lib/dev/concat-lib/src/pb/modules/pb.utils.js',
          'lib/dev/concat-lib/src/pb/modules/pb.lang.js',
          'lib/dev/concat-lib/src/pb/modules/pb.listtable.js',
          'lib/dev/concat-lib/src/pb/modules/pb.edit-form-modal.js',
          'lib/dev/concat-lib/src/pb/modules/pb.splitted-view.js',

          'lib/dev/concat-lib/src/pb/modules/pb.fileupload.js',
          'lib/dev/concat-lib/src/pb/modules/pb.fileupload.imageuploader.js',
          'lib/dev/concat-lib/src/pb/modules/pb.fileupload.imageinput.js',
          'lib/dev/concat-lib/src/pb/modules/pb.fileupload.fileinput.js',

          'lib/dev/concat-lib/src/pb/modules/pb.summernote.js',
          'lib/dev/concat-lib/src/pb/modules/pb.summernote-image-uploader.js',

          'lib/dev/concat-lib/src/pb/modules/pb.editors.js',
          
          'lib/dev/concat-lib/src/pb/pb.admin.js',

        ],
        dest: 'lib/dev/concat-lib/dist/pb-admin.js'
      },

      'concat-all-main' : {
        src: [
          'lib/dev/concat-lib/dist/defaults-main.js',
          'lib/dev/concat-lib/dist/jquery-default-plugins-main.js',
          'lib/dev/concat-lib/dist/pb-main.js',
        ],
        dest: 'lib/dev/comp-lib/all-main.js'
      },

      'concat-all-admin' : {
        src: [
          'lib/dev/concat-lib/dist/defaults-admin.js',
          'lib/dev/concat-lib/dist/jquery-default-plugins-admin.js',
          'lib/dev/concat-lib/dist/pb-admin.js',
        ],
        dest: 'lib/dev/comp-lib/all-admin.js'
      }

    },
    
    less : {
      build : {
        options : {
          ieCompat : true,
          paths: ["lib/dev/css/"]
        },
        files: [{
          expand: true,
          cwd: 'lib/dev/less',
          src: ['pb-main.less', 'pb-admin.less'],
          dest: 'lib/dev/css/',
          rename : function(dest, src){
            return dest + src.replace('.less','.css');
          }
        },
        {
          expand: true,
          cwd: 'lib/dev/less/pages',
          src: ['**/*.less'],
          dest: 'lib/dev/css/pages/',
          rename : function(dest, src){
            return dest + src.replace('.less','.css');
          }
        },
        {
          expand: true,
          cwd: 'lib/dev/less/page-builder',
          src: ['**/*.less'],
          dest: 'lib/dev/css/page-builder/',
          rename : function(dest, src){
            return dest + src.replace('.less','.css');
          }
        },

        ]
      }
    },

    copy : {
      dist :{
        files: [{
          expand: true,
          cwd: 'lib/dev',
          src: [
            '**/*',
            '!**/*.css',
            '!**/*.js',
            '!**/*.less',
            '!**/concat-lib/**',
            '!**/less/**'],
          dest: 'lib/dist'
        }]
      }
    },

    uglify: {
      build: {
        files: [{
          expand: true,
          cwd: 'lib/dev',
          src: [
            '**/*.js',
            '!**/concat-lib/**',
          ],
          dest: 'lib/dist'
        }]
      }
    },

    cssmin : {
      minify: {
        expand: true,
        cwd: 'lib/dev/',
        src: [
          '**/*.css',
          '!**/concat-lib/**'
        ],
        dest: 'lib/dist/'
      }
    },

    watch: {
      js : {
        files: [
          'lib/dev/concat-lib/**/*.js',
          'lib/dev/js/**/*.js',
        ],
        tasks: ['build-js']
      },
      css : {
        files: [
          'lib/dev/concat-lib/**/*.css',
        ],
        tasks: ['build-css']
      },
      less : {
        files: [
          'lib/dev/less/**/*.less'
        ],
        tasks: ['build-css']
      }
    },
  });

  grunt.registerTask('build-js', [
    'concat:defaults-admin',
    'concat:jquery-default-plugins-admin',
    'concat:pb-admin',
    
    'concat:defaults-main',
    'concat:jquery-default-plugins-main',
    'concat:pb-main',

    'concat:concat-all-admin',
    'concat:concat-all-main'
  ]);
  grunt.registerTask('build-css', ['less']);
  grunt.registerTask('build', ['clean','build-js','build-css']);
  grunt.registerTask('dist', ['build', 'uglify', 'cssmin', 'copy:dist']);
  grunt.registerTask('default', ['build']);

};
