#!/usr/bin/env bash
parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )

cd "$parent_path"

mkdir -p ../lib
cd ../lib
wget https://jqueryui.com/resources/download/jquery-ui-1.12.1.zip -O jquery-ui.zip
unzip jquery-ui.zip
mv jquery-ui-1.12.1 jquery-ui