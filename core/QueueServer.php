<?php
/**
 * Created by Yuankui
 * Date: 2018/12/24
 * Time: 10:26
 */

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
        $this->server = new Swoole\WebSocket\Server($sConfig['host'], $sConfig['port']);

        $this->server->on('start', [$this, 'start']);
        $this->server->on('open', [$this, 'open']);
        $this->server->on('request', [$this, 'request']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);
    }

    public function start(Swoole\WebSocket\Server $server)
    {
        if(empty($this->config['redis'])) {
            die('redis config lack');
        }
        $redis     = new RedisTopic($this->config['redis']);
        $allTopics = $redis->getAllTopic();

        foreach ($allTopics as $topic) {
            $queue = new SplQueue();
            $queue->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);

            $this->queue[$topic] = $queue;
        }
    }

    public function open(Swoole\WebSocket\Server $server, $request)
    {
        shell_exec('echo \'server: handshake success with fd{$request->fd}\r\n\' > /root/yb.log');
    }

    public function request($request, $response)
    {
        $message = json_decode($request->get['message'], true);

        if(isset($message['topic']) && !empty($message['data']) && isset($this->queue[$message['topic']])) {
            $this->queue[$message['topic']]->enqueue($message['data']);
        }
    }

    public function message(Swoole\WebSocket\Server $server, $frame)
    {
        $rData = json_decode($frame->data, true);

        if(isset($rData['model']) && $rData['model'] == 'sub' && $rData['topic']) {
            $data = $this->queue[$rData['topic']]->dequeue();

            $server->push($frame->fd, json_encode($data));
        }
    }

    public function close(Swoole\WebSocket\Server $server, $fd)
    {
        shell_exec('echo \'client: fd{'.$fd.'} close\r\n\' > /root/yb.log');
    }

    public function serverStart()
    {
        $this->server->start();
    }
}