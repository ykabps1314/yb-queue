<?php
/**
 * Created by PhpStorm.
 * User: yk
 * Date: 19-2-19
 * Time: 上午10:46
 */

namespace core\server;


class QueueServer
{
    private $server;

    private $config;

    public function __construct($config)
    {
        $this->config = $config;

        if(empty($this->config['server']['host']) || empty($this->config['server']['port'])) {
            exit('the necessary configuration lacked');
        }

        $this->server = new \Swoole\Server($this->config['server']['host'], $this->config['server']['port'], SWOOLE_BASE, SWOOLE_SOCK_TCP);

        //加载服务配置，主要是静态资源相关解析指向
        if(isset($this->config['server']['setting']))
            $this->server->set($this->config['server']['setting']);

        $this->server->on('start', [$this, 'start']);
        $this->server->on('workerStart', [$this, 'workerStart']);
        $this->server->on('connect', [$this, 'connect']);
        $this->server->on('receive', [$this, 'receive']);
        $this->server->on('close', [$this, 'close']);
    }
    public function start(\Swoole\Server $server)
    {
        //给主进程命名，方便为之后的热重启shell命令做准备
        swoole_set_process_name('yb98k_yb_queue');
    }
    public function workerStart(\Swoole\Server $server,  $workerId)
    {
        //将框架的主要资源文件和配置进行加载，常驻内存
        include __DIR__ . '/../../vendor/autoload.php';
    }

    public function connect(\Swoole\Server $server, $fd)
    {
        //TODO 连接创建
    }

    public function receive(\Swoole\Server $server, $fd, $reactorId, $data)
    {
        $getData = json_decode($data, true);

        if(isset($getData['action']) && $getData['action'] == 'product') {
            //生产者进入

            $redisClient = new \Swoole\Redis();

            $proData = json_encode([
                'consumerRouter' => $getData['consumerRouter'] ?? '',
                'data' => $getData['data'] ?? ''
            ]);
            $queue = $getData['queue'] ?? '';
            $redisConfig = $this->config['redis'][$getData['channel'] ?? ''] ?? '';

            if($redisConfig && $queue) {
                $redisClient->connect(
                    $redisConfig['host'],
                    $redisConfig['port'],
                    function (\Swoole\Redis $client, $result) use ($queue, $proData) {
                        $client->lpush($queue, $proData, function (\Swoole\Redis $client, $result) {
                            if($result <= 0) {
                                //TODO 判断是否成功 --失败可以发送告警
                            }
                        });
                    });
            }

            $server->send($fd, 200);
        } else {
            $server->send($fd, 400);
        }

        $server->close($fd);
    }

    public function close(\Swoole\Server $server, $fd)
    {
        //TODO 连接关闭
    }

    public function run()
    {
        $this->server->start();
    }
}