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

    protected $linkStatus = false;

    public function __construct($host, $port = 9901, $topic = 'default_topic')
    {
        //初始化连接配置
        $this->host  = $host;
        $this->port  = $port;
        $this->topic = $topic;

        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $this->client->on('connect', [$this, 'connect']);
        $this->client->on('receive', [$this, 'receive']);
        $this->client->on('error', [$this, 'error']);
        $this->client->on('close', [$this, 'close']);

        $this->client->connect($this->host, $this->port);
    }

    public function connect(Swoole\Client $cli)
    {
        $this->linkStatus = true;
        echo '连接成功'.PHP_EOL;
    }

    public function receive(Swoole\Client $cli, $data)
    {

    }

    public function error(Swoole\Client $cli)
    {
        die('发生错误');
    }

    public function close(Swoole\Client $cli)
    {

    }

    public function sendMsg($data)
    {
        while (!$this->linkStatus) {
            sleep(1);
        }

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
