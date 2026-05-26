<?php
// +----------------------------------------------------------------------
// | File：NacosManager.php
// +----------------------------------------------------------------------
// | Created：2026/5/26 10:49
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------
// | Author: zhangjian <83680989@qq.com>
// +----------------------------------------------------------------------
namespace App\Rpc;

use EasySwoole\Rpc\NodeManager\NodeManagerInterface;
use EasySwoole\Rpc\ServiceNode;

class NacosManager implements NodeManagerInterface
{
    function getServiceNodes(string $serviceName, ?string $version = null): array
    {

    }

    function getServiceNode(string $serviceName, ?string $version = null): ?ServiceNode
    {

    }

    function deleteServiceNode(ServiceNode $serviceNode): bool
    {

    }

    function serviceNodeHeartBeat(ServiceNode $serviceNode): bool
    {

    }

}