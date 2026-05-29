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


use EasySwoole\AtomicLimit\AtomicLimit;
use EasySwoole\Component\Di;


class Goods extends BaseService
{

    function serviceName(): string
    {
        return 'Goods';
    }

    public function List()
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