<?php
namespace Home\Model;
use Think\Model;

class OrderRefundModel extends Model {
    
    public function addOrderRefund($orderSn, $ticketNo, $userId, $money, $retype, $salonId, $rereason)
    {
        $thrift = D('ThriftHelper');
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddOrderRefundParam();
        $param->money = $money;
        $param->orderSn = $orderSn;
        $param->rereason = $rereason;
        $param->retype = $retype;
        $param->salonId = $salonId;
        $param->ticketNo = $ticketNo;
        $param->userId = $userId;
        /*
        return $thrift->request('trade-center', 'addOrderRefund', array($orderSn, $ticketNo, $userId, $money, $retype, $salonId, $rereason));
         */
        return $thrift->request('trade-center', 'addOrderRefund', array($param));
    }
    
    public function getRefundByTicketNo($ticketNo)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getOrderRefundByTicketNo', array($ticketNo));
    }
}
