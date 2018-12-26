<?php

$config = require(__DIR__ . '/config/main.php');
require(__DIR__ . '/core/QueueServer.php');

if(empty($config['redis'])) {
    die('redis config lack');
}

$redis     = new RedisTopic($config['redis']);
$allTopics = $redis->getAllTopic();
$queues = [];
foreach ($allTopics as $topic) {
    $queue = new SplQueue();
    $queue->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);

    $queues[$topic] = $queue;
}

$queueServer = new QueueServer($config);
$queueServer->setQueues($queues);