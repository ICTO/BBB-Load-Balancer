BBB Load balancer
========================

This project was developed to load balance BigBlueButton servers.
It is written in PHP and based on the Symfony Standard Edition framework.
A web application is included to manage servers and users, but it can also be managed with the API. The API docs can be found on the web app.

The loadbalancer uses the same API as the default [BBB API](https://code.google.com/p/bigbluebutton/wiki/API).

The following API methods are currently supported:

- create
- join
- end
- isMeetingRunning
- getMeetingInfo
- getMeetings

The following API methods are currently **NOT** supported:

- getDefaultConfigXML
- setConfigXML
- getRecordings
- publishRecordings
- deleteRecordings

# Quick setup #

You can use a chef recipe to setup your server or setup a vagrant box.

[https://github.com/brunogoossens/BBBLB-Chef-cookbook](https://github.com/brunogoossens/BBBLB-Chef-cookbook)

# Setup #

Dependencies

	sudo apt-get install php5-common
	sudo apt-get install php5
	sudo apt-get install php5-xcache
	sudo apt-get install php5-mongo
	sudo apt-get install php5-fpm
	sudo apt-get install php5-curl
	sudo apt-get install mongodb
	sudo apt-get install npm

Get the code

	git clone git@github.com:brunogoossens/BBB-Load-Balancer.git /var/www/bbb-load-balancer
	cd /var/www/bbb-load-balancer

Get NPM packages

	$ npm install

Edit the config file

	$ cp app/config/parameters.yml.dist app/config/parameters.yml

Change the bbb.salt value inside the file. The salt must be the same on all BBB servers
You can also change other values if you like.

Get composer

	$ curl -s https://getcomposer.org/installer | php

Update and install packages with composer

	$ composer update

Start server (without apache or nginx)

	$ app/console server:run --env=prod

If you want to configure an other server like apache or nginx, you can follow [this](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html) guide.

To automatically enable and disable servers based on there status, you can add this cronjob.

    * * * * * /path/to/project/app/console bbblb:servers:check --env=prod

To remove stopped meetings from the load balancer.

	* * * * * /path/to/project/app/console bbblb:meetings:cleanup --env=prod
