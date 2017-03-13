<?php
/**
 * 用户
 * @author carson
 */
namespace Home\Model;

class PaymentlogModel extends \Think\Model {
    
    public function addPaymentLog($tn, $ordersn, $amount, $device, $logType, $payid)
    {
        $thrift = D('ThriftHelper');
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddPaymentLogParam();
        $param->amount = $amount;
        $param->device = $device;
        $param->logType = $logType;
        $param->ordersn = $ordersn;
        $param->payid = $payid;
        $param->tn = $tn;
        return $thrift->request('trade-center', 'addPaymentLog', array($param));
        /*
        return $thrift->request('trade-center', 'addPaymentLog', array($tn, $ordersn, $amount, $device, $logType, $payid));
         */
    }
    
    public function getPaymentLogByOrderSn($orderSn)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getPaymentLogByOrderSn', array($orderSn));
    }
    
}
