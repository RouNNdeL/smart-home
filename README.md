# Status
[![Build Status](https://travis-ci.org/RouNNdeL/smart-home.svg?branch=master)](https://travis-ci.org/RouNNdeL/smart-home) [![website Status](https://img.shields.io/website/https/home.zdul.xyz.svg)](https://home.zdul.xyz/) ![](https://img.shields.io/snyk/vulnerabilities/github/rounndel/smart-home.svg?style=flat) [![dependencies Status](https://david-dm.org/rounndel/smart-home/status.svg)](https://david-dm.org/rounndel/smart-home) [![devDependencies Status](https://david-dm.org/rounndel/smart-home/dev-status.svg)](https://david-dm.org/rounndel/smart-home?type=dev) ![](https://img.shields.io/github/languages/count/rounndel/smart-home.svg?style=flat)

# Introduction
This is my private Smart Home web server setup. Devices can be controlled with the Google Assistant, from a Web App (WIP) and an Android App (WIP). Some other functionality, ex. shared family shopping list is planned as well.

List of available devices:
- PC LED Controller ([MCU C code](https://github.com/RouNNdeL/led-controller-avr), [Windows Service](https://github.com/RouNNdeL/led-controller-node))
- WiFi LED Controller ([ESP8266 Arduino code](https://github.com/RouNNdeL/esp8266-leds))
- WiFi Dimmable Lamp ([ESP8266 Arduino code](https://github.com/RouNNdeL/esp8266-lamp))
- WiFi IR Remote ([ESP8266 Arduino code](https://github.com/RouNNdeL/esp8266-remote))

# Schematics
All devices are custom designed with all files being open source. Schematic and board files are located in each repository's *_schematics* directory, or in case of the PC LED Controller [here](https://github.com/RouNNdeL/led-controller-pcb).

# Database
The project uses a MySQL database hosted on my private server.
![database_model](https://github.com/RouNNdeL/smart-home/blob/master/database_model.png?raw=true)