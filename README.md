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
- isMeetingRunning
- getMeetings

The following API methods are currently **NOT** supported:

- getDefaultConfigXML
- setConfigXML
- getRecordings
- publishRecordings
- deleteRecordings

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
	sudo npm install uglifycss -g
	sudo npm install uglify-js -g

Get the code

	git clone git@github.com:brunogoossens/BBB-Load-Balancer.git /var/www/bbb-load-balancer
	cd /var/www/bbb-load-balancer

Edit the config file

	cp app/config/parameters.yml.dist app/config/parameters.yml

Change the bbb.salt value. The salt must be the same on all BBB servers
You can also change other values if you like.

```
parameters:
    mongo_server: mongodb://localhost:27017
    mongo_database: BBBLoadBalancer

    mailer_transport: smtp
    mailer_host: smtp.ugent.be
    mailer_user: null
    mailer_password: null

    locale: en
    secret: ThisTokenIsNotSoSecretChangeIt
    debug_toolbar: true
    debug_redirects: false
    use_assetic_controller: true

    app.site_name: BBB Load Balancer
    app.domain: domainname.com
    app.email_name: BBB Load Balancer
    app.email: test@example.com
    app.email_noreply: no-reply@example.com

    bbb.salt: thesaltonthebbbservers
```

Get composer

	curl -s https://getcomposer.org/installer | php

Update and install packages with composer

	composer update

Start server (without apache or nginx)

	app/console server:run --env=prod

If you want to configure an other server like apache or nginx, you can follow [this](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html) guide.