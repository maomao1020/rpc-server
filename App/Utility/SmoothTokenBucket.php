<?php
// +----------------------------------------------------------------------
// | File：SmoothTokenBucket.php
// +----------------------------------------------------------------------
// | Created：2026/5/29 10:33
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------
// | Author: zhangjian <83680989@qq.com>
// +----------------------------------------------------------------------
namespace App\Utility;

use EasySwoole\Component\Singleton;
use Swoole\Table;

class SmoothTokenBucket
{
    use Singleton;

    private Table $table;

    public function initTable($maxRows = 1024)
    {
        $this->table = new Table($maxRows);
        // tokens: 剩余令牌数 (浮点数，支持微观产生 0.001 个令牌)
        $this->table->column('tokens', Table::TYPE_FLOAT);
        // last_time: 上一次请求进入的精确时间戳 (微秒级浮点数)
        $this->table->column('last_time', Table::TYPE_FLOAT);
        $this->table->create();
    }

    /**
     * 核心平滑限流判定方法
     * @param string $key 限流的唯一标识（如 "rpc:UserService:getUser"）
     * @param float $capacity 桶的最大容量（允许的最大突发并发量）
     * @param float $rate 每秒钟注入桶中的令牌数（即预期的平滑 QPS 限制线）
     * @return bool 是否放行
     */
    public function access(string $key, float $capacity, float $rate): bool
    {
        if (!$this->table) {
            return true; // 防御：若未初始化 Table 则默认放行
        }

        $now = microtime(true);

        // 1. 读取当前 Key 的内存状态
        $row = $this->table->get($key);

        if (!$row) {
            // 首次请求：初始化满桶，并扣除当前请求的 1 个令牌
            $initialTokens = max(0.0, $capacity - 1.0);
            $this->table->set($key, [
                'tokens'    => $initialTokens,
                'last_time' => $now
            ]);
            return true;
        }

        $lastTime      = (float)$row['last_time'];
        $currentTokens = (float)$row['tokens'];

        // 2. 【核心平滑点】增量计算时间差内新产生的令牌数
        $deltaTime = max(0.0, $now - $lastTime); // 计算时间差（秒）
        $generated = $deltaTime * $rate;        // 这一小段时间内应该生成的令牌数

        // 最新总令牌数（不能超过桶的最高容量容量）
        $newTokens = min($capacity, $currentTokens + $generated);

        // 3. 令牌扣减判定
        if ($newTokens >= 1.0) {
            // 令牌足够：放行，扣除 1 个令牌，更新最新时间戳
            $this->table->set($key, [
                'tokens'    => $newTokens - 1.0,
                'last_time' => $now
            ]);
            return true;
        }

        // 令牌不足：拦截，但也要更新时间戳
        // 核心原因：若不更新时间戳，当下一次大流量在一微秒后进来时，deltaTime 会极小，导致令牌永远无法根据 delta 递增蓄水。
        $this->table->set($key, [
            'tokens'    => $newTokens,
            'last_time' => $now
        ]);
        return false;
    }
}