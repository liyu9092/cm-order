<?php
namespace Home\Model;
use Think\Model;

class VoucherTrendModel extends BaseModel {
    
    public function addTrend($vId, $vSn, $userId, $orderSn, $status){

        $param = new \cn\choumei\thriftserver\service\stub\gen\VoucherTrendParam();
        $param->vAddTime = time();
        $param->vBindId = 0;
        $param->vId = $vId;
        $param->vOrderSn = $orderSn;
        $param->vSn = $vSn;
        $param->vStatus = $status;
        $param->vUserId = $userId;
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'addVoucherTrend', array($param));
    }
}
?>
