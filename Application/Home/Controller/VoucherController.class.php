<?php

namespace Home\Controller;

class VoucherController extends OrderOutController{
    
    //根据购买的项目，获取符合条件的代金券
    /** 
    * 1.可使用项目类别
    * 2.限制类型
    * 3.代金券时间
    * 4.满足金额
    * 5.代金券状态 未使用
    * 6.首单
    */
    public function chooseVoucher(){        
        if(empty($this->userId)){
            $this->error(1);
        }
        $userId = $this->userId;
        $params = $this->param;
        // 初始化返回数据		
        $this->ret = array(
                "main"=>array(),
                "other"=>array(
                        "totalNum"=>0
                )
        );
                
        $page = intval($params['page']);
        $page = $page ? $page : 1;
        //print_r($paramData);exit;
        $num = intval($params['pageSize']);
        $num = $num ? $num : 10;
        $totalNum = 0;
       
        $allVoucherInfo = array();
        $allUniqueVoucherIds = array();
        //普通订单或购物车订单
        $voucherObj = D('Voucher');
        if($params['shopcartSn']){
            $shopcartSn = $params['shopcartSn'];
            $allUniqueVoucherIds = $voucherObj->getVoucherIds($userId,$shopcartSn,2);
            //print_r($allUniqueVoucherIds);exit;
            if(!$allUniqueVoucherIds){
                $haveVoucherId = 0; //没有可用的代金券
            }else{
                $haveVoucherId = 1;
                 //获取代金券信息
                foreach ($allUniqueVoucherIds as $key => $value) {
                    $resVoucherInfo = $voucherObj->getVoucherInfoByVid($value);
                    $allVoucherInfo[] = $resVoucherInfo;
                }
            }                      
        }else if($params['orderSn']){
            $orderSn = $params['orderSn'];
            $allUniqueVoucherIds = $voucherObj->getVoucherIds($userId,$orderSn,1);
            if(!$allUniqueVoucherIds){
                $haveVoucherId = 0; //没有可用的代金券
            }else{
                $haveVoucherId = 1;
                //获取代金券信息
                foreach ($allUniqueVoucherIds as $key => $value) {
                    $resVoucherInfo = $voucherObj->getVoucherInfoByVid($value);
                    $allVoucherInfo[] = $resVoucherInfo;
                }
            }
                      
        }else{
            $this->error(1);
        }
     
        if($haveVoucherId){
            //从所有可用的代金券中取出需要的条数
            $count = count($allVoucherInfo);
            if( empty($totalNum) )
                $this->ret["other"]["totalNum"] = $count ?: 0;
            else
                $this->ret["other"]["totalNum"] = $totalNum ?: 0;

            foreach ($allVoucherInfo as $key2 => $value2) {
                if($key2 >= ($page-1)*$num  && $key2 < $page * $num){
                    $resAllVoucherInfo[] = $value2;
                }
            }
        }else{
            $this->error(7008);
        }
        
         $this->ret["main"] = $resAllVoucherInfo;
         $this->success();
        
        
    }
    
    //支付前，先确认代金券是否符合条件,符合条件进行绑定
    public function useVoucherToPay(){

        $paramData = $this->param;
        if(empty($this->userId)){
            $this->error(1);
        }
        $orderSn   = $paramData['orderSn'];
        if(!$orderSn || !$paramData['vId']) $this->error(1);

        /*
        $user_id = $this->userId;
        $orderInfo = D('Order')->getOrderOrderItemInfo($orderSn,$user_id);
        if(!$orderInfo) $this->error(2002);

        $order      = $orderInfo['order'];
        $orderItem = $orderInfo['order_item'];
        if(!$order || !$orderItem) $this->error(2002);
         */
        
        $user_id = $this->userId;
        $OrderObj=D('Order');
        $orderInfo = $OrderObj->getOrderbySn($orderSn);
        if(empty($orderInfo)) $this->error(2002);
        $orderItemInfo = D('OrderItem')->getOrderItemByOrderId($orderInfo['orderId']);
        if(empty($orderItemInfo)) $this->error(2002);
        //传入代金券vId
        $vId = intval($paramData['vId']);          
        $voucherObj = D('Voucher');
        $vidCanBeUsedRes = $voucherObj->isVidCanBeUsed($vId,$user_id,$orderSn);
        //echo $vidCanBeUsedRes;exit;
        if(!$vidCanBeUsedRes){
            $this->error(7005);
        }
        //绑定订单和代金券
        /*
        $bindRes = $voucherObj->bindVoucherAndOrder($vId,$user_id,$orderSn);
        if($bindRes === false){
            $this->error(7006);
        }
         */
        $salonName = D('Salon')->getSalonNameById($orderItemInfo['salonId']);
        $bindRes = $voucherObj->bindOrder($vId, $orderSn, $orderItemInfo['salonId'], $salonName, $orderItemInfo['itemId'], $orderItemInfo['itemname']);
        if(!$bindRes)
            $this->error(7006);
        //如果当前有动态记录，不需要再次添加,比如用户取消支付，然后继续支付
        $trendRes = $voucherObj->isSameVoucherTrend($vId,$orderSn);
        if(empty($trendRes)){
            //增加一条动态记录到cm_voucher_trend表
            $vStatus = 1;
            $voucherObj->addVoucherTrend($vId,$user_id,$orderSn,$vStatus);
        }
        //获取抵用金额
        $useMoneyByVoucher = $voucherObj->getUseMoneyByVoucherId($orderSn,$vId);
        //获取订单的价格
        /*
        $priceall = M('order')->where(array('ordersn' => $orderSn))->getField('priceall');
         */
        $priceall = $orderInfo['priceall'];
        //修改订单的实付金额
        if($useMoneyByVoucher){
            $actuallyPay = $priceall - $useMoneyByVoucher;
            /*
            M('order')->where(array('ordersn' => $orderSn))->save(array('actuallyPay' => $actuallyPay));
             */
            D('Order')->updateOrderActuallyPay($orderSn, $actuallyPay);
        }
        $this->success();      
    }
    
        
}    