<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Copyright (c) 2017 Sebastian Schedlbauer
 * LoggerAppenderLogzIO uses sockets to transmit messages to logz.io
 */
class LoggerAppenderLogzIO extends LoggerAppender
{


    /**
     * Target host.
     * @see http://php.net/manual/en/function.fsockopen.php
     */
    protected $remoteHost = "listener.logz.io";
    protected $port = 5050;


    // ******************************************
    // *** Appender methods                   ***
    // ******************************************


    public function activateOptions()
    {
        if (empty($this->remoteHost)) {
            $this->warn("Required parameter [remoteHost] not set. Closing appender.");
            $this->closed = true;
            return;
        }
        if (empty($this->port)) {
            $this->warn("Required parameter [port] not set. Closing appender.");
            $this->closed = true;
            return;
        }
        if (empty($this->token)) {
            $this->warn("Required parameter [token] not set. Closing appender.");
            $this->closed = true;
            return;
        }

    }

    public function append(LoggerLoggingEvent $event)
    {

        $loggerName = $event->getLoggerName();
        $thread = $event->getThreadName();
        $message = (string)$event->getRenderedMessage();
        $lvl = (string )$event->getLevel();

        $objectToSend = array("token" => $this->getToken(), "message" => $message, "loglevel" => $lvl, "loggername" => $loggerName);
        $additionalFields = explode(",", $this->getAdditionalFields());
        if (is_array($additionalFields) && count($additionalFields) > 0) {
            foreach ($additionalFields as $additionalField) {
                switch (strtolower($additionalField)) {
                    case 'thread':
                        $objectToSend['thread'] = $thread;
                        break;
                    case 'hostname':
                        $objectToSend['hostname'] = gethostname();
                        break;
                }
            }
        }


        $ser = json_encode($objectToSend);

        try {
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock === false) {
                $this->warn('Cannot create socket. Closing appender.');
                $this->closed = true;
                return;
            }
            $conn = socket_connect($sock, $this->remoteHost, $this->port);
            if ($conn === false) {
                $this->warn('Cannot connect socket. Closing appender.');
                $this->closed = true;
                return;
            }
            $sent = socket_write($sock, $ser, strlen($ser));
            if ($sent === false) {
                $this->warn('Cannot write to socket. Closing appender.');
                $this->closed = true;
                return;
            }
            socket_close($sock);
        } catch (Exception $e) {
            $this->warn('Appending failed. ' . $e->getMessage() . ' .Closing appender.');
            $this->closed = true;
            return;
        }
    }

    // ******************************************
    // *** Accessor methods                   ***
    // ******************************************

    /** Sets the target host. */
    public function setRemoteHost($hostname)
    {
        $this->setString('remoteHost', $hostname);
    }

    /** Sets the target host. */
    public function setToken($token)
    {
        $this->setString('token', $token);
    }

    /** Sets the target port */
    public function setPort($port)
    {
        $this->setPositiveInteger('port', $port);
    }

    /** Sets the additional fields port */
    public function setAdditionalFields($fields)
    {
        $this->setString('additionalFields', $fields);
    }


    /** Returns the target host. */
    public function getRemoteHost()
    {
        return $this->getRemoteHost();
    }

    /** Returns the target port. */
    public function getPort()
    {
        return $this->port;
    }

    /** Returns the token. */
    public function getToken()
    {
        return $this->token;
    }

    /** Returns the additional fields. */
    public function getAdditionalFields()
    {
        return $this->additionalFields;
    }


}
