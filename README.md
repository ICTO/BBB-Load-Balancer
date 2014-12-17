BBB Load balancer
========================

This project was developed to load balance BigBlueButton servers.
It is written in PHP and based on the Symfony Standard Edition.
A web application is included to manage servers and users.

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

# Installation #