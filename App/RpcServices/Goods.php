<?php
// +----------------------------------------------------------------------
// | File：Goods.php
// +----------------------------------------------------------------------
// | Created：2026/5/23 16:04
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------
// | Author: zhangjian <83680989@qq.com>
// +----------------------------------------------------------------------
namespace App\RpcServices;


use EasySwoole\Rpc\AbstractService;


class Goods extends AbstractService
{

    function serviceName(): string
    {
        return 'Goods';
    }

    function list()
    {
        var_dump($this->request()->toArray());
        $this->response()->setResult([
            [
                'id'   => 1,
                'name' => 'zhang',
                'time' => time(),
            ]
        ]);
        $this->response()->setMsg('Get User List Successfully');
    }
}