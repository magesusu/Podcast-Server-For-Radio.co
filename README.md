# Podcast Server For Radio.co
Using Radio.co's account and PHP server, you can create a podcast automatically. 
High performance version of Podcast-Creator-For-Radio.co

##Features
* Copy all media files to your PHP server via Radio.co FTP.
* Create rss to comply with itunes podcast standard.
* [NEW] Fetch radio.co media information into podcast details.
* [NEW] We gathered the configuration files in one.

_This software downloads directly to your PHP server, not dropbox.
If you want to use the dropbox function, please use java version._

##Getting started

###Requirement
* A radio.co Account.
* A PHP server (Those with cron function are recommended).

###Installation
1. Clone this repository to your php server
2. Edit settings.php
3. Execute index.php (It is recommended to run at intervals in Cron)


##Reference Sites

http://php-archive.net/php/ftp-transmission

http://www.zedwood.com/article/php-calculate-duration-of-mp3

http://mio-koduki.blogspot.jp/2012/08/phpcurl-phpcurlgoogle.html
