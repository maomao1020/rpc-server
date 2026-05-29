<?php
// +----------------------------------------------------------------------
// | File：BaseService.php
// +----------------------------------------------------------------------
// | Created：2026/5/29 10:00
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------
// | Author: zhangjian <83680989@qq.com>
// +----------------------------------------------------------------------
namespace App\RpcServices;

use App\Utility\SmoothTokenBucket;
use EasySwoole\AtomicLimit\AtomicLimit;
use EasySwoole\Component\Di;
use EasySwoole\Rpc\AbstractService;

abstract class BaseService extends AbstractService
{


    protected function onRequest(?string $action): ?bool
    {

        $token = sprintf('RPC:%s:%s', $this->serviceName(), $action);
        if (!SmoothTokenBucket::getInstance()->access($token, 100,1)) {
            throw new \Exception('PC Request Rate Limit Exceeded', 429);
        }
        return parent::onRequest($action);
    }
}