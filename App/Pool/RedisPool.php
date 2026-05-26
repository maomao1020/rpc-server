<?php
// +----------------------------------------------------------------------
// | File：RedisPool.php
// +----------------------------------------------------------------------
// | Created：2026/5/23 17:47
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------
// | Author: zhangjian <83680989@qq.com>
// +----------------------------------------------------------------------
namespace App\Pool;

use EasySwoole\EasySwoole\Config;
use EasySwoole\Pool\AbstractPool;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;

class RedisPool extends AbstractPool
{
    protected function createObject()
    {
        $config = Config::getInstance()->getConf('REDIS');
        return new Redis(new RedisConfig($config));
    }
}