<?php


namespace EasySwoole\EasySwoole;


use App\RpcServices\Goods;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\NodeManager\RedisManager;
use EasySwoole\Rpc\Rpc;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $arr = Config::getInstance()->getConf('REDIS');
        $redisPool  = new RedisPool(new RedisConfig($arr));
        $manager = new RedisManager($redisPool);
        $config     = new \EasySwoole\Rpc\Config();
        $containerIp = current(swoole_cpu_num() ? swoole_get_local_ip() : []);
        $serverIp = !empty($containerIp) ? $containerIp : '0.0.0.0';
        var_dump($serverIp);
        $config->setServerIp($serverIp);
        $config->setNodeManager($manager);
        $config->getBroadcastConfig()->setEnableBroadcast(false);//启用广播
        $config->getBroadcastConfig()->setEnableListen(false);   //启用监听
        $rpc = new Rpc($config);
        // 添加 Goods 服务到服务管理器中
        $rpc->add(new Goods());
        $rpc->attachToServer(ServerManager::getInstance()->getSwooleServer());
    }
}