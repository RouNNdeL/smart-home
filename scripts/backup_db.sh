#!/usr/bin/env bash

if [ "$#" -ne 3 ]; then
    echo "3 arguments required (db user, db password, dir)"
    exit
fi

mysqldump -u $1 -p$2 smart_home > $3/$(date +%Y-%m-%d).sql
ls $3 -t | tail -n +6 | xargs rm --