#!/usr/bin/env bash

npm install
npm run-script build
composer install
composer dump-autoload -o
