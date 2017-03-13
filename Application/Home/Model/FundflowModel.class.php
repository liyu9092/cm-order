<?php
/**
 * 订单处理类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class FundflowModel extends BaseModel {
  
     //通过ticketNo找到pay_type和money
    public function getFundflowArr($ticketNo){
        $thrift = D('ThriftHelper');
        $funds = $thrift->request('trade-center', 'getFundflowArr', array($ticketNo));
        if(empty($funds))
            return null;
        $ret = array();
        foreach($funds as $fund)
        {
            $ret[] = array(
                'pay_type' => $fund['payType'],
                'money' => $fund['money'],
            );
        }
        return $ret;
        /*
        $fundflowArr = M('fundflow')->field('pay_type,money')->where("ticket_no = '%s'",$ticketNo)->select();
        return $fundflowArr;
         */
    }
    
    public function addFundflow($orderSn, $ticketNo, $userId, $money, $payType, $salonId, $codeType)
    {
        $thrift = D('ThriftHelper');
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddFundflowParam();
        $param->codeType = $codeType;
        $param->fftype = 1;
        $param->money = $money;
        $param->orderSn = $orderSn;
        $param->payType = $payType;
        $param->salonId = $salonId;
        $param->ticketNo = $ticketNo;
        $param->userId = $userId;
        /*
        return $thrift->request('trade-center', 'addFundflow', array($orderSn, $ticketNo, $userId, $money, $payType, $salonId, $codeType));
         */
        return $thrift->request('trade-center', 'addFundflow', array($param));
    }
    
}
