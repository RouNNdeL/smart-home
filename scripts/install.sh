#!/usr/bin/env bash

git submodule init
git sumbodule sync
git sumbodule update

npm install
npm run-script build

sh download_jquery.sh

composer install
composer dump-autoload -o
