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
        $this->server = new Swoole\WebSocket\Server($sConfig['host'], $sConfig['port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

        $this->server->on('workerStart', [$this, 'workerStart']);
        $this->server->on('open', [$this, 'open']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);

        $this->server->start();
    }

    public function workerStart(Swoole\WebSocket\Server $server)
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
        shell_exec('echo \'server: handshake success with fd{'.$request->fd.'}\r\n\' > /root/yb-request.log');
    }

    public function message(Swoole\WebSocket\Server $server, $frame)
    {
        $message = json_decode($frame->data, true);

        //生产
        if(isset($message['topic']) && !empty($message['data']) && isset($this->queue[$message['topic']])) {
            $this->queue[$message['topic']]->enqueue($message['data']);
        }

        //消费
        if(isset($message['model']) && $message['model'] == 'sub' && $message['topic']) {
            $data = $this->queue[$message['topic']]->dequeue();

            $server->push($frame->fd, json_encode($data));
        }
    }

    public function close(Swoole\WebSocket\Server $server, $fd)
    {
        shell_exec('echo \'client: fd{'.$fd.'} close\r\n\' > /root/yb-close.log');
    }
}