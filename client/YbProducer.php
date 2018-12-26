<?php
/**
 * Created by Yuankui
 * Date: 2018/12/25
 * Time: 16:57
 */

class YbProducer {

    protected $client;

    protected $host;

    protected $port;

    protected $topic;

    public function __construct($host, $port = 9901, $topic = 'default_topic')
    {
        //初始化连接配置
        $this->host  = $host;
        $this->port  = $port;
        $this->topic = $topic;

        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP);

        $this->client->connect($this->host, $this->port, 1000);
    }

    public function sendMsg($data)
    {
        $pubData = json_encode([
            'topic' => $this->topic,
            'data' => $data,
        ]);

        $this->client->send($pubData);
    }
}

$ybProducer = new YbProducer('127.0.0.1');
for ($i = 1;$i < 10;$i++) {
    $ybProducer->sendMsg(['test' => $i]);
    echo '生产第'.$i.'份数据'.PHP_EOL;
    sleep(1);
}
