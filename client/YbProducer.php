<?php
/**
 * Created by Yuankui
 * Date: 2018/12/25
 * Time: 16:57
 */

class YbProducer {

    protected $socket;

    protected $host;

    protected $port;

    protected $topic;

    public function __construct($host, $port = 9901)
    {
        //初始化连接配置
        $this->host = $host;
        $this->port = $port;

        $this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);

        if(!$this->socket) {
            die(socket_strerror(socket_last_error()));
        }

        $this->connect();
    }

    protected function connect()
    {
        $result = socket_connect($this->socket, $this->host, $this->port);

        if(!$result) {
            die(socket_strerror(var_export(socket_last_error(), true)));
        }
        echo '连接成功';
    }

    public function setTopic($topic = 'default_topic')
    {
        $this->topic = $topic;
    }

    public function publish($data)
    {
        $pubData = json_encode([
            'topic' => $this->topic,
            'data' => $data,
        ]);

        if(!socket_write($this->socket, $pubData)) {
            die(socket_strerror(var_export(socket_last_error(), true)));
        }
    }

    public function close()
    {
        socket_close($this->socket);
    }
}

for ($i = 1;$i < 10;$i++) {
    $ybProducer = new YbProducer('127.0.0.1');
    $ybProducer->setTopic();
    $ybProducer->publish(['test' => $i]);
    sleep(1);
}
