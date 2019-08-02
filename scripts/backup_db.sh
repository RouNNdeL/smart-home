#!/usr/bin/env bash

if [ "$#" -ne 3 ]; then
    echo "3 arguments required (db user, db password, dir)"
    exit
fi

mysqldump -u $1 -p$2 smart_home > $3/smart_home_$(date +%Y-%m-%d_%H:%M:%S).sql
ls $3 -t | tail -n +15 | xargs rm --