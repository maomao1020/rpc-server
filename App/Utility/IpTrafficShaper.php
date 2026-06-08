<?php
// +----------------------------------------------------------------------
// | File：IpTrafficShaper.php
// +----------------------------------------------------------------------
// | Created：2026/6/8 10:52
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------
// | Author: zhangjian <83680989@qq.com>
// +----------------------------------------------------------------------
namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

class IpTrafficShaper
{
    use Singleton;

    /**
     * 检测IP是否触发限流
     * @param string $ip 用户IP
     * @param int $windowSeconds 窗口时间（秒）
     * @param int $maxRequests 最大允许请求数
     * @return bool true: 已限流/拒绝访问, false: 允许通过
     */
    public function isLimiting(string $ip, int $windowSeconds = 60, int $maxRequests = 100): bool
    {
        $now = (int)(microtime(true) * 1000);
        $windowMs = $windowSeconds * 1000;
        $startTime = $now - $windowMs;
        $key = "ip_limit:" . md5($ip);

        // 2. 借助 RedisPool 执行管道/事务命令
        return RedisPool::invoke(function (Redis $redis) use ($key, $now, $startTime, $maxRequests, $windowSeconds) {

            // 移除当前窗口时间之前的旧请求记录（滑动窗口核心）
            $redis->zRemRangeByScore($key, '0', (string)$startTime);

            // 获取当前窗口内的总请求次数
            $currentRequests = $redis->zCard($key);

            if ($currentRequests >= $maxRequests) {
                return true; // 超过限制，触发限流
            }

            // 记录本次请求
            $redis->zAdd($key, $now, (string)$now);

            // 防止冷IP长期占用内存，每次请求刷新Key的生命周期（窗口时间的2倍）
            $redis->expire($key, $windowSeconds * 2);

            return false; // 未超过限制，放行
        }, 'redis'); // 这里的 'redis' 为连接池名称
    }
}