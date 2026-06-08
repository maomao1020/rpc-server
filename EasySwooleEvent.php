<?php


namespace EasySwoole\EasySwoole;


use App\Rpc\NacosManager;
use App\RpcServices\Goods;
use App\RpcServices\Order;
use App\Utility\IpTrafficShaper;
use App\Utility\SmoothTokenBucket;
use EasySwoole\AtomicLimit\AtomicLimit;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Rpc\Rpc;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST,function (Request $request,Response $response){
            $fd = $request->getSwooleRequest()->fd;
            $clientInfo =  ServerManager::getInstance()->getSwooleServer()->getClientInfo($fd);
            $ip = $clientInfo['remote_ip'] ?? '127.0.0.1';
            // 设定规则：每 10 秒内，单个 IP 最多请求 20 次
            $isBlock = IpTrafficShaper::getInstance()->isLimiting($ip, 10, 20);

            if ($isBlock) {
                // 拦截请求并直接响断点状态码
                $response->withStatus(429);
                $response->write(json_encode([
                    'code' => 429,
                    'msg'  => 'Too Many Requests. Please try again later.'
                ], JSON_UNESCAPED_UNICODE));

                return false; // 返回 false 结束当前请求流程
            }
        });
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
        $rpc->add(new Order());
        SmoothTokenBucket::getInstance()->initTable(2048);
        $swooleServer = ServerManager::getInstance()->getSwooleServer();
        $rpc->attachToServer($swooleServer);
    }
}