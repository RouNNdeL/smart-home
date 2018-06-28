/*
 * MIT License
 *
 * Copyright (c) 2018 Krzysztof "RouNdeL" Zdulski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: {
            all: ["dist"],
            js: ["dist/js"],
            css: ["dist/css"]
        },
        browserify: {
            build: {
                options: {
                    alias: {
                        'jQuery': 'jquery'
                    },
                    transform: [["babelify"]],
                },
                files: [{
                    expand: true,
                    cwd: "src/js",
                    src: ["*.js"],
                    dest: 'dist/js',
                }]
            },
            dev: {
                options: {
                    alias: {
                        'jQuery': 'jquery'
                    },
                    browserifyOptions:{
                        debug: true
                    },
                    transform: [["babelify"]],
                },
                files: [{
                    expand: true,
                    cwd: "src/js",
                    src: ["*.js"],
                    dest: 'dist/js',
                    ext: ".min.js"
                }]
            }
        },
        uglify: {
            build: {
                options: {
                    compress: {
                        drop_console: true,
                        dead_code: true
                    },
                    sourceMap: true
                },
                files: [{
                    expand: true,
                    cwd: "dist/js",
                    src: ["*.js", "!*.min.js"],
                    dest: "dist/js",
                    ext: ".min.js"
                }]
            }
        },
        jshint: {
            options: {
                "esversion": 6
            },
            build: ["Gruntfile.js", "src/js/*.js"]
        },
        csscomb: {
            build: {
                files: [{
                    expand: true,
                    cwd: 'src/sass',
                    src: ['*.scss', '!mixins.scss'],
                    dest: 'src/sass'
                }]
            }
        },
        sasslint: {
            options: {
                "configFile": ".sasslintrc"
            },
            build: {
                files: [{
                    expand: true,
                    cwd: 'src/sass',
                    src: ['*.scss', '!mixins.scss']
                }]
            }
        },
        sass: {
            build: {
                options: {
                    style: "compressed"
                },
                files: [{
                    expand: true,
                    cwd: 'src/sass',
                    src: ['*.scss'],
                    dest: 'dist/css',
                    ext: '.min.css'
                }]
            }
        }
    });

    grunt.loadNpmTasks('grunt-browserify');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-uglify-es');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-sass-lint');
    grunt.loadNpmTasks('grunt-babel');
    grunt.loadNpmTasks('grunt-csscomb');

    grunt.registerTask('default', ['clean:all', 'jshint', 'browserify:build', 'uglify', 'csscomb', 'sasslint', 'sass']);
    grunt.registerTask('css', ['clean:css', 'csscomb', 'sasslint', 'sass']);
    grunt.registerTask('js', ['clean:js', 'jshint', 'browserify:build', 'uglify']);
    grunt.registerTask('js-dev', ['clean:js', 'jshint', 'browserify:dev']);
    grunt.registerTask('nolint', ['clean:all', 'browserify', 'uglify', 'sass']);
};