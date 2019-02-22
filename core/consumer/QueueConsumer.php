<?php
/**
 * Created by PhpStorm.
 * User: yk
 * Date: 19-2-19
 * Time: 下午5:36
 */

namespace core\consumer;


abstract class QueueConsumer
{
    abstract public function handle(String $data);
}