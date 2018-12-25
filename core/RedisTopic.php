<?php
/**
 * Created by Yuankui
 * Date: 2018/12/25
 * Time: 14:58
 */

class RedisTopic
{
    private $redis;

    private $host = '127.0.0.1';

    private $port = 6379;

    public $rTopicKey;

    public $scPrefixKey;

    public function __construct($config = [])
    {

        $this->host        = $config['host'];
        $this->port        = $config['port'];
        $this->rTopicKey   = $config['rTopicKey'];
        $this->scPrefixKey = $config['scPrefixKey'];

        $this->redis = new Redis();

        $this->redis->connect($this->host, $this->port) || die("Redis连接失败！");
    }

    public function getAllTopic()
    {
        $allTopics = $this->redis->sMembers($this->rTopicKey);

        if(empty($allTopics)) {
            $this->redis->sAdd($this->rTopicKey, 'default_topic');
            $allTopics[] = 'default_topic';
        }

        return $allTopics;
    }
}