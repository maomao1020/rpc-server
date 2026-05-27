<?php


namespace EasySwoole\EasySwoole;


use App\Rpc\NacosManager;
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
        // $redisPool  = new RedisPool(new RedisConfig($arr));
        // $manager = new RedisManager($redisPool);
        $manager     = new NacosManager();
        $config      = new \EasySwoole\Rpc\Config();
        $containerIp = current(swoole_cpu_num() ? swoole_get_local_ip() : []);
        $serverIp    = !empty($containerIp) ? $containerIp : '0.0.0.0';
        $config->setServerIp($serverIp);
        $config->setNodeManager($manager);
        $config->setListenPort(9503);
        // 【优化建议】既然已经用了 Redis 作为注册中心，建议关闭 UDP 广播功能
        // Docker 跨容器默认不支持 UDP 广播，保留它们只会徒增报错和资源消耗
        $config->getBroadcastConfig()->setEnableBroadcast(false);//启用广播
        $config->getBroadcastConfig()->setEnableListen(false);   //启用监听
        $rpc = new Rpc($config);
        // 添加 Goods 服务到服务管理器中
        $rpc->add(new Goods());
        $rpc->attachToServer(ServerManager::getInstance()->getSwooleServer());
    }
}