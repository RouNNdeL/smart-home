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
            js: ["dist/js", "dist/build"],
            css: ["dist/css", "dist/build"]
        },
        browserify: {
            options: {
                alias: {
                    'jQuery': 'jquery'
                }
            },
            build: {
                files: [{
                    expand: true,
                    cwd: "dist/build/js/es5",
                    src: ["*.js"],
                    dest: 'dist/build/js/browserify',
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
                    sourceMap: false
                },
                files: [{
                    expand: true,
                    cwd: "dist/build/js/browserify",
                    src: ["*.js", "!*.min.js"],
                    dest: "dist/js",
                    ext: ".min.js"
                }]
            }
        },
        cssmin: {
            build: {
                files: [{
                    expand: true,
                    cwd: 'dist/build/css',
                    src: ['*.css', '!*.min.css'],
                    dest: 'dist/css',
                    ext: '.min.css'
                }]
            }
        },
        jshint: {
            options: {
                "esversion": 6
            },
            build: ["Gruntfile.js", "src/js/*.js"]
        },
        babel: {
            options: {
                sourceMap: true,
                presets: ["env"]
            },
            build: {
                files: [{
                    expand: true,
                    cwd: 'src/js',
                    src: ['*.js', '!*.min.js'],
                    dest: 'dist/build/js/es5',
                    ext: '.js'
                }]
            }
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
                files: [{
                    expand: true,
                    cwd: 'src/sass',
                    src: ['*.scss'],
                    dest: 'dist/build/css',
                    ext: '.css'
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

    grunt.registerTask('default', ['clean:all', 'jshint', 'babel', 'browserify', 'uglify', 'csscomb', 'sasslint', 'sass', 'cssmin']);
    grunt.registerTask('css', ['clean:css', 'csscomb', 'sasslint', 'sass', 'cssmin']);
    grunt.registerTask('js', ['clean:js', 'jshint', 'babel', 'browserify', 'uglify']);
    grunt.registerTask('nolint', ['clean:all', 'browserify', 'babel', 'uglify', 'sass', 'cssmin']);
};