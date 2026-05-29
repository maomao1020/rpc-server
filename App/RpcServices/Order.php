<?php
// +----------------------------------------------------------------------
// | File：Order.php
// +----------------------------------------------------------------------
// | Created：2026/5/29 8:54
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------
// | Author: zhangjian <83680989@qq.com>
// +----------------------------------------------------------------------
namespace App\RpcServices;

class Order extends BaseService
{
    public function serviceName(): string
    {
        return "Order";
    }

    public function List()
    {
        $this->response()->setResult([
            [
                'order_no'    => sprintf('%s%s', date('YmdHis', time()), rand(100, 999)),
                'create_time' => date('Y-m-d H:i:s', time()),
                'creater'     => 'jerry',
            ],
            [
                'order_no'    => sprintf('%s%s', date('YmdHis', time()), rand(100, 999)),
                'create_time' => date('Y-m-d H:i:s', time()),
                'creater'     => 'zhang',
            ],
        ]);
        $this->response()->setMsg('Get User List Successfully');
    }
}