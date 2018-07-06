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
    const external = ["jQuery", "tether", "bootstrap"];
    const dist_js = "dist/js";
    const dist_css = "dist/css";
    const src_js = "src/js";
    const src_sass = 'src/sass';
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: {
            all: ["dist/*", "!dist/vendor/**"],
            vendor: ["dist/vendor"],
            js: [dist_js],
            css: [dist_css]
        },
        browserify: {
            options: {
                transform: [["babelify"]],
                alias: {
                    'jQuery': 'jquery'
                }

            },
            build: {
                options: {
                    external: external
                },
                files: [{
                    expand: true,
                    cwd: src_js,
                    src: ["*.js", "!_*.js"],
                    dest: dist_js,
                }]
            },
            dev: {
                options: {
                    browserifyOptions: {
                        debug: true
                    },
                    external: external
                },
                files: [{
                    expand: true,
                    cwd: src_js,
                    src: ["*.js", "!_*.js"],
                    dest: dist_js,
                    ext: ".js"
                }]
            },
            "dev-fast": {
                options: {
                    browserifyOptions: {
                        debug: true
                    },
                    external: external
                },
                files: [{
                    expand: true,
                    cwd: src_js,
                    src: ["*.js", "!_*.js"],
                    dest: dist_js,
                    ext: ".min.js"
                }]
            },
            vendor: {
                options: {
                    require: ['jquery', "tether", "bootstrap", "jquery-ui"]
                },
                src: [],
                dest: 'dist/vendor/js/vendor.js',
            },
            "vendor-dev": {
                options: {
                    browserifyOptions: {
                        debug: true
                    },
                    require: ['jquery', "tether", "bootstrap", "jquery-ui"]
                },
                src: [],
                dest: 'dist/vendor/js/vendor.js',
            }
        },
        exorcise: {
            dev: {
                files: [{
                    expand: true,
                    cwd: dist_js,
                    src: ["*.js"],
                    dest: dist_js,
                    ext: ".js.map"
                }]
            },
            vendor: {
                files: [{
                    expand: true,
                    cwd: "dist/vendor/js",
                    src: ["*.js", "!*.min.js"],
                    dest: 'dist/vendor/js',
                    ext: ".js.map"
                }]
            }
        },
        uglify: {
            options: {
                sourceMapIn: function(n) {
                    return n + ".map";
                }
            },
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
                    cwd: dist_js,
                    src: ["*.js", "!*.min.js"],
                    dest: dist_js,
                    ext: ".min.js"
                }]
            },
            dev: {
                options: {
                    compress: {
                        drop_console: false,
                        dead_code: false
                    },
                    sourceMap: true
                },
                files: [{
                    expand: true,
                    cwd: dist_js,
                    src: ["*.js", "!*.min.js"],
                    dest: dist_js,
                    ext: ".min.js"
                }]
            },
            vendor: {
                options: {
                    compress: {
                        drop_console: true,
                        dead_code: true
                    },
                    sourceMap: false
                },
                files: [{
                    expand: true,
                    cwd: "dist/vendor/js",
                    src: ["*.js", "!*.min.js"],
                    dest: 'dist/vendor/js',
                    ext: ".min.js"
                }]
            },
            "vendor-dev": {
                options: {
                    compress: {
                        drop_console: false,
                        dead_code: false
                    },
                    sourceMap: true
                },
                files: [{
                    expand: true,
                    cwd: "dist/vendor/js",
                    src: ["*.js", "!*.min.js"],
                    dest: 'dist/vendor/js',
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
                    cwd: src_sass,
                    src: ['*.scss'],
                    dest: src_sass
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
                    cwd: src_sass,
                    src: ['*.scss']
                }]
            }
        },
        sass: {
            options: {
                outputStyle: "compressed",
                implementation: require("node-sass"),
            },
            build: {
                options: {
                    sourceMap: false
                },
                files: [{
                    expand: true,
                    cwd: src_sass,
                    src: ['*.scss'],
                    dest: dist_css,
                    ext: '.min.css'
                }]
            },
            dev: {
                options: {
                    sourceMap: true
                },
                files: [{
                    expand: true,
                    cwd: src_sass,
                    src: ['*.scss'],
                    dest: dist_css,
                    ext: '.min.css'
                }]
            },
            vendor: {
                options: {
                    sourceMap: false
                },
                files: [{
                    expand: true,
                    cwd: 'src/',
                    src: ['vendor.scss'],
                    dest: 'dist/vendor/css',
                    ext: '.min.css'
                }]
            },
            "vendor-dev": {
                options: {
                    sourceMap: true
                },
                files: [{
                    expand: true,
                    cwd: 'src/',
                    src: ['vendor.scss'],
                    dest: 'dist/vendor/css',
                    ext: '.min.css'
                }]
            }
        },
        copy: {
            build: {
                files: [{
                    expand: true,
                    cwd: "node_modules/ion-rangeslider/img",
                    src: ['*.png'],
                    dest: 'dist/img'
                }]
            }
        },
        jsonlint: {
            build: {
                files: [{
                    expand: true,
                    src: ['*.json', ".babelrc", ".sasslintrc"]
                }, {
                    expand: true,
                    cwd: "_lang",
                    src: ["**/*.json"]
                }]
            }
        }
    });

    grunt.loadNpmTasks('grunt-browserify');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-uglify-es');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-sass-lint');
    grunt.loadNpmTasks('grunt-babel');
    grunt.loadNpmTasks('grunt-csscomb');
    grunt.loadNpmTasks('grunt-exorcise');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-jsonlint');

    grunt.registerTask('vendor', ['jsonlint', 'clean:vendor', 'browserify:vendor', 'uglify:vendor', 'sass:vendor']);
    grunt.registerTask('vendor-dev', [
        'jsonlint',
        'clean:vendor',
        'browserify:vendor-dev', "exorcise:vendor", 'uglify:vendor-dev',
        'sass:vendor-dev'
    ]);
    grunt.registerTask('default', [
        'jsonlint',
        'clean:all',
        'jshint', 'browserify:build', 'uglify:build',
        'sasslint', 'sass:build',
        'copy:build'
    ]);
    grunt.registerTask('dev', [
        'jsonlint',
        'clean:all',
        'jshint', 'browserify:dev', "exorcise:dev", 'uglify:dev',
        'csscomb', 'sasslint', 'sass:dev',
        'copy:build'
    ]);
    grunt.registerTask('dev-fast', [
        'jsonlint',
        'clean:all',
        'jshint', 'browserify:dev-fast', "exorcise:dev",
        'csscomb', 'sasslint', 'sass:dev',
        'copy:build'
    ]);
    grunt.registerTask('css', ['jsonlint', 'clean:css', 'csscomb', 'sasslint', 'sass:build']);
    grunt.registerTask('js', ['jsonlint', 'clean:js', 'jshint', 'browserify:build', 'uglify:build']);
};