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