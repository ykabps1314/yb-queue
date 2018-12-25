<?php
/**
 * Created by Yuankui
 * Date: 2018/12/25
 * Time: 16:58
 */

class YbCustomer {

    protected $socket;

    protected $host;

    protected $port;

    public function __construct($host, $port = 9901)
    {
        //初始化连接配置
        $this->host = $host;
        $this->port = $port;

        $this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);

        if($this->socket < 0) {
            die(socket_strerror($this->socket));
        }

        $this->connect();
    }

    protected function connect()
    {
        $result = socket_connect($this->socket, $this->host, $this->port);

        if($result < 0) {
            die(socket_strerror($this->socket));
        }
    }

    public function sendSub($topic = 'default_topic')
    {
        $data = json_encode([
            'model' => 'sub',
            'topic' => $topic
        ]);

        if(!socket_write($this->socket, $data, strlen($data))) {
            die(socket_strerror($this->socket));
        }
    }

    public function subscribe()
    {
        $subData = '';

        while ($out = socket_read($this->socket, 2048)) {
            $subData .= $out;
        }

        return $subData;
    }

    public function close()
    {
        socket_close($this->socket);
    }
}

$ybCustomer = new YbCustomer('127.0.0.1');
$ybCustomer->sendSub();
var_dump($ybCustomer->subscribe());