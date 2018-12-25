<?php

$config = require(__DIR__ . '/config/main.php');
require(__DIR__ . '/core/QueueServer.php');

$queueServer = new QueueServer($config);

$queueServer->serverStart();