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
            throw new Exception(socket_strerror($this->socket));
        }

        $this->connect();
    }

    protected function connect()
    {
        $result = socket_connect($this->socket, $this->host, $this->port);

        if($result < 0) {
            throw new Exception(socket_strerror($this->socket));
        }
    }

    protected function sendSub($topic = 'default_topic')
    {
        $data = json_encode([
            'model' => 'sub',
            'topic' => $topic
        ]);

        if(!socket_write($this->socket, $data, strlen($data))) {
            throw new Exception(socket_strerror($this->socket));
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