# log4php-logz.io
Appender for log4php to transmit messages to logz.io using UDP sockets

###Usage:

Copy LoggerAppenderLogzIO.php into your log4php appender directory and make sure that the append is registered e.g. in your LoggerAutoloader.php: 'LoggerAppenderLogzIO' => '/appenders/LoggerAppenderLogzIO.php'

###XML sample configuration:

```xml
<appender name="LogzAppender" class="LoggerAppenderLogzIO">
    <param name="remoteHost" value="listener.logz.io" />
    <param name="port" value="5050" />
    <param name="token" value="xxxxxx" />
    <param name="additionalFields" value="thread,hostname" />
</appender>
```

Parameters remoteHost and port are usally fixed, but can bet overridden in XML. The token is required and can be found under your logz.io settings.

By default the following fields are transmitted to logz4php: "loglevel","message","loggername"

additionalFields is optional and determines which additional values should be transfered to logz.io. Currently following fields are supported:

thread : Thread ID
hostname: Hostname of system

