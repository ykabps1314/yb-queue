<?php
/**
 * Created by PhpStorm.
 * User: yk
 * Date: 19-2-19
 * Time: 下午1:44
 */

include __DIR__ . '/main/ProductClient.php';

class YbClient extends ProductClient {

    public function sendMsg($data)
    {
        return $this->send($data);
    }
}

$config = [
    'host' => '127.0.0.1',
    'port' => 9009
];

$setting = [
    'channel' => 'default',
    'queue'   => 'default',
    'consumerRouter'  => 'consumer\test\index'
];

$msg = 'sadasdasdasdas';

var_dump((new YbClient($config))->set($setting)->sendMsg($msg));