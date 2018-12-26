<?php
/**
 * Created by Yuankui
 * Date: 2018/12/24
 * Time: 10:26
 */

require __DIR__.'/RedisTopic.php';

class QueueServer {

    public $server;

    public $config;

    /**
     * @var SplQueue Array
     */
    public $queue;

    public function __construct($config)
    {
        if(empty($config['server'])) {
            die('server config lack');
        }
        $this->config = $config;

        //绑定的服务
        $sConfig      = $config['server'];
        $this->server = new swoole_websocket_server($sConfig['host'], $sConfig['port']);

        $this->server->on('open', [$this, 'open']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);
//        $this->server->on('request', [$this, 'request']);

        $this->server->start();
    }

    public function setQueues($queues)
    {
        $this->queue = $queues;
    }

    public function open(swoole_websocket_server $server, $request)
    {
        var_dump($request);
        shell_exec('echo \'server: handshake success with fd{'.$request->fd.'}\r\n\' > /root/yb-request.log');
    }

    public function request($request, $response)
    {
        var_dump($request->get);
        $message = json_decode($request->get['message'], true);

        if(isset($message['topic']) && !empty($message['data']) && isset($this->queue[$message['topic']])) {
            $this->queue[$message['topic']]->enqueue($message['data']);
        }
    }

    public function message(swoole_websocket_server $server, $frame)
    {
        var_dump($frame->data);
        $rData = json_decode($frame->data, true);

        if(isset($rData['model']) && $rData['model'] == 'sub' && $rData['topic']) {
            $data = $this->queue[$rData['topic']]->dequeue();

            $server->push($frame->fd, json_encode($data));
        }
    }

    public function close(swoole_websocket_server $server, $fd)
    {
        shell_exec('echo \'client: fd{'.$fd.'} close\r\n\' > /root/yb-close.log');
    }
}