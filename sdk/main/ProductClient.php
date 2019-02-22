<?php
/**
 * Created by PhpStorm.
 * User: yk
 * Date: 19-2-19
 * Time: 上午11:39
 */

abstract class ProductClient
{
    const ACTION = 'product';

    protected $channel = 'default';

    protected $queue = 'default';

    protected $consumerRouter;

    protected $client;

    public function __construct($config)
    {
        if(empty($config['host']) || empty($config['port'])) {
            exit('the necessary configuration lacked');
        }

        $this->client = new \Swoole\Client(SWOOLE_SOCK_TCP);

        if (!$this->client->connect($config['host'], $config['port'], -1))
        {
            exit("connect failed. Error: {$this->client->errCode}\n");
        }
    }

    public function set(Array $data)
    {
        foreach ($data as $key => $value) {
            if($key == 'client') continue;

            if(property_exists(self::class, $key))
                $this->{$key} = $value;
        }

        return $this;
    }

    public function setChannel(String $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    public function setQueue(String $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    public function setConsumerRouter(String $consumerRouter)
    {
        $this->consumerRouter = $consumerRouter;
        return $this;
    }

    protected function send($data)
    {
        $sendData = [
            'action'            => self::ACTION,
            'channel'           => $this->channel,
            'queue'             => $this->queue,
            'consumerRouter'    => $this->consumerRouter,
            'data'              => $data,
        ];

        $this->client->send(json_encode($sendData));
        $tag = (int)$this->client->recv() === 200 ? true : false;
        $this->client->close();

        return $tag;
    }

    abstract public function sendMsg($data);
}