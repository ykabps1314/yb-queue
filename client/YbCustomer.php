<?php
/**
 * Created by Yuankui
 * Date: 2018/12/25
 * Time: 16:58
 */

require __DIR__.'/WebSocketClient.php';

class YbCustomer {

    protected $client;

    protected $host;

    protected $port;

    public function __construct($host, $port = 9901)
    {
        //初始化连接配置
        $this->host = $host;
        $this->port = $port;

        $this->client = new WebSocketClient($this->host, $this->port);

        if(!$this->client->connect()) {
            die('connect failed');
        }
    }

    public function sendSub($topic = 'default_topic')
    {
        $data = json_encode([
            'model' => 'sub',
            'topic' => $topic
        ]);

        if(!$this->client->send($data)) {
            return false;
        }

        return true;
    }

    public function subscribe()
    {
        return $this->client->recv();
    }
}

$ybCustomer = new YbCustomer('127.0.0.1');
$ybCustomer->sendSub();
var_dump($ybCustomer->subscribe());