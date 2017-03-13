<?php
/**
 * 代金券处理类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class VoucherModel extends BaseModel {
    
    //根据订单号，项目金额，获取代金券抵用金额
    public function getUseMoneyByVoucher($ordersn,$orderPrice){
        /*
       $vUseMoney =  $this->where(array('vOrderSn' => $ordersn))->getField('vUseMoney');
       if(empty($vUseMoney)){
           $UseMoneyByVoucher = 0 ;
       }else{
           if($vUseMoney >= $orderPrice){
               $UseMoneyByVoucher = $orderPrice;
           }else{
               $UseMoneyByVoucher = $vUseMoney;
           }
       }
       return $UseMoneyByVoucher;
         */
       
        $voucher = $this->getVocherByOrdersn($ordersn);
        if(empty($voucher))
           return 0;
       
        $vUseMoney = $voucher['vUseMoney'];
        if($vUseMoney >= $orderPrice)
            return $orderPrice;
        else
            return $vUseMoney;
        
    }
    
    //根据代金券id，订单号，获取抵用金额
    public function getUseMoneyByVoucherId($orderSn,$vId){
        /*
        $vUseMoney =  $this->where(array('vId' => $vId))->getField('vUseMoney');
        $orderPrice = M('order')->where(array('ordersn' => $orderSn))->getField('priceall');
         */
        $orderPrice = D('Order')->getOrderBySn($orderSn)['priceall'];
        $vUseMoney = $this->getVoucherById($vId)['vUseMoney'];
        if(empty($vUseMoney)){
            $UseMoneyByVoucher = 0 ;
        }else{
            if($vUseMoney >= $orderPrice){
                $UseMoneyByVoucher = $orderPrice;
            }else{
                $UseMoneyByVoucher = $vUseMoney;
            }
        }
        return $UseMoneyByVoucher;
        
    }


    //根据购物车号，获取项目信息，主要是itemid
    /**
     * @deprecated since version thrift
     * @param type $shopcartSn
     */
    public function getItemInfoByShopcartSn($shopcartSn){
        /*
        $shopcartInfo = M('shopcart')->where(array('shopcartsn' => $shopcartSn))->select();
        if(!$shopcartInfo){
            $this->error = 7002;
            return false;
        }
        return $shopcartInfo;
         */
        
    }
    
    //根据订单号获取订单信息
    /**
     * @deprecated since version thrift
     * @param type $orderSn
     */
    public function getItemInfoByOrderSn($orderSn){
        /*
        $orderItemInfo = M('order_item')->where(array('ordersn' => $orderSn))->find();
        if(!$orderItemInfo){
            $this->error = 7001;
            return false;
        }
        return $orderItemInfo;
         */
    }
    
    //单个订单项目或购物车项目获取符合条件所有代金券id
    public function getVoucherIds($userId,$Sn,$type){
        //1 订单
        //2 购物车
        if($type == 1){
            $uniqueVoucherWithOrder = $this->getOrderVoucherIds($userId,$Sn);
                                             
        }else if($type == 2){
            /*
           $allOrderSn =  M('order')->field('ordersn')->where(array('shopcartsn' => $Sn))->select();
             */
            $allOrderSn = D('Order')->getOrderByShopcartSn($Sn);
           //print_r($allOrderSn);exit;
           foreach ($allOrderSn as $key => $value) {
               $orderVoucherIds = $this->getOrderVoucherIds($userId,$value['ordersn']);
               //获得所有订单的代金券,可能有重复
               $allVoucherIds[] = $orderVoucherIds;
           }
           //print_r($allVoucherIds);exit;
           foreach ($allVoucherIds as $key2 => $value2) {
               foreach($value2 as $key3 => $value3){
                   $shopcartVoucherIds[] = $value3;
               }
           }
           //print_r($shopcartVoucherIds);exit;
           /*
            Array
            (
                [0] => 19
                [1] => 19
                [2] => 19
                [3] => 19
                [4] => 21
            )
            */
           //将所有获得的代金券id去重
           $uniqueVoucherWithOrderRes = array_unique($shopcartVoucherIds);
           /**
            * Array
            (
                [0] => 19
                [4] => 21
            )
            */
           $uniqueVoucherWithOrder = array_values($uniqueVoucherWithOrderRes);
           
        }else{
            $this->error(7003);
            return false;
        }
        return $uniqueVoucherWithOrder;
    }
    
    
    
    
    
    //根据项目id (数组), 用户id 获取符合条件的代金券
    //根据购买的项目，获取符合条件的代金券
    /** 
    * 1.可使用项目类别
    * 2.限制类型
    * 3.代金券时间
    * 4.满足金额
    * 5.代金券状态 未使用
    * 6.首单
    */
    //每一个订单有哪些券可以使用
    public function getOrderVoucherIds($userId ,$orderSn){
        //获取是否是首单
        $res = $this->IsFirstOrder($userId);
        if($res){
            $IsFirst = 2;
        }else{
            $IsFirst = 1;
        }
        $orderVoucherInfo = $this->getOrderVoucherInfo($orderSn);
        
        if(!$orderVoucherInfo){
            return false;
        }
        // 根据用户id + 可使用项目类别 + 使用限制类型 + 需满足金额 + 可使用结束时间 + 代金券的状态        
        $time =time();
        /*
        $where[] = "vUserId = {$userId}";
        //$where[] = "find_in_set({$orderVoucherInfo['typeId']},vUseItemTypes)";
        $where[] = "vUseNeedMoney <= {$orderVoucherInfo['itemPrice']} ";
        $where [] = "{$time} BETWEEN IF(vUseStart=0,'1400000000',vUseStart) and IF(vUseEnd=0,'1600000000',vUseEnd)";
        $where[] = "vStatus = 1";
        //选出了单个订单所有符合条件的代金券(没有判断是否是首单)
        $voucherInfo = $this->where(implode(' AND ',$where))->order('vUseMoney desc')->select();
         */
        $vouchers = $this->getVouchersByUserId($userId, 1);
        $voucherInfo = array();
        foreach($vouchers as $voucher)
        {
            if($voucher['vUseNeedMoney'] > $orderVoucherInfo['itemPrice'])
                continue;
            if($voucher['vUseStart'] > 0 && $time < $voucher['vUseStart'])
                continue;
            if($voucher['vUseEnd'] > 0 && $time > $voucher['vUseEnd'])
                continue;
            $voucherInfo[] = $voucher;
        }
        foreach ($voucherInfo as $key => $value) {
            //判断typeid是否符合
            if(empty($value['vUseItemTypes'])){
                //如果是首单，进行判断
                if(strpos($value['vUseLimitTypes'],"2") === false){                
                    $voucherIds[] =  $value['vId'];
                }else{              
                    if($IsFirst == 2){
                       $voucherIds[] =  $value['vId'];
                    }
                }
            }else{
                if(strpos($value['vUseItemTypes'],(string)$orderVoucherInfo['typeId']) !== false){
                    //如果是首单，进行判断
                    if(strpos($value['vUseLimitTypes'],"2") === false){                
                        $voucherIds[] =  $value['vId'];
                    }else{              
                        if($IsFirst == 2){
                           $voucherIds[] =  $value['vId'];
                        }
                    }
                }
            }
            
            
            
        }
        return $voucherIds ;     
    }
    
    //对每一个订单信息进行组装
    public function getOrderVoucherInfo($orderSn){
        /*
        $orderItemInfo = M('order_item')->where(array('ordersn' => $orderSn))->find();
        if(!$orderItemInfo){
            $this->error = 7001;
            return false;
        }
        //获取订单项目信息中的价格，项目id
        $itemId = $orderItemInfo['itemid'];
        $itemPrice = $orderItemInfo['price_dis'];
        //根据itemId 获取此项目的类别
        $salonItemInfo = M('salon_item')->field('typeid,item_type')->where(array('itemid' => $itemId))->find();
        if($salonItemInfo['item_type'] == 1){
            $typeId = $salonItemInfo['typeid'];
        }else{
            $typeId = 101 ;   //显示特价，类型值统一为101
        }
         */
        $order = D('Order')->getOrderBySn($orderSn);
        if(empty($order))
        {
            $this->error = 7001;
            return false;
        }
        $orderItem = D('OrderItem')->getOrderItemByOrderId($order['orderId']);
        $itemId = $orderItem['itemId'];
        $itemPrice = $orderItem['priceall'];
        $item = D('SalonItem')->getItemById($itemId);
        if($item['itemType'] == 1)
            $typeId = $item['typeid'];
        else
            $typeId = 101;
        $orderVoucherInfo = array(
            'itemId' => $itemId,
            'itemPrice' => $itemPrice,
            'typeId' => $typeId,
            'orderSn' => $orderSn,
        );
        return $orderVoucherInfo;
    }
    //判断是否是首单
     public function IsFirstOrder($userId){
       //获取首单的订单号
         /*
       $order = M("order")->where(array('user_id'=>$userId,"ispay" => 2))->order('orderid')->find();
       if(!$order){
           return true;  //是首单
       }else{
           return false; //不是首单
       }
       */
        $order = D('Order')->getFirstOrderIspay2($userId);
        if(empty($order))
            return true;
        else
            return false;
    }
    
    //根据代金券vid获取代金券信息
    public function getVoucherInfoByVid($vId){
        /*
       $voucherInfo =  $this->where(array('vId' => $vId))->find();
         */
        $voucherInfo = $this->getVoucherById($vId);
       $vUseMoney = $voucherInfo['vUseMoney'];
       $vcTitle = $voucherInfo['vcTitle'];
       $vUseStart = $voucherInfo['vUseStart'];
       $vUseEnd = $voucherInfo['vUseEnd'];
       $vUseNeedMoney =  $voucherInfo['vUseNeedMoney'];
       $vUseItemTypes =  $voucherInfo['vUseItemTypes'];
       $vUseLimitTypes =  $voucherInfo['vUseLimitTypes'];
       
       if(strpos($vUseLimitTypes,"2") === false){   
           $vUseLimitTypesValue = "";
       }else{
           $vUseLimitTypesValue = "首单支付"; //是首单
       }      
       
       //拼接使用条件
        $limit = "";
        $allItemTypes = array(1, 2, 3, 4, 5, 7, 8, 101);
        if ($vUseNeedMoney) {  //只选中了限制金额
            $limit.="满{$vUseNeedMoney}元可用;";
        }
        if ($vUseLimitTypesValue) {
            $limit.=$vUseLimitTypesValue . "可用;";
        }
        //$vUseItemTypesArray = array_filter(explode(",",$vUseItemTypes));
        $vUseItemTypesValue = '';
        if(!empty($vUseItemTypes)){
           $itemIdArray = array_filter(explode(',', $vUseItemTypes));
            foreach ($itemIdArray as $key => $value) {
                //根据itemId 获取此项目的类别
                 //$salonItemInfo = M('salon_item')->field('typeid,item_type')->where(array('itemid' => $value))->find();
                /*
                 if($salonItemInfo['item_type'] == 1){
                     $typeId = $salonItemInfo['typeid'];
                     //根据typeid 获取type名称
                     $typeName = M('salon_itemtype')->where(array('typeid' => $typeId))->getField('typename');
                 }else{
                     $typeId = 101 ;   //显示特价，类型值统一为101
                     $typeName = '限时特价';
                 }
                 * 
                 */
                 if($value == 101){
                     $typeName = '限时特价';
                 }else{
                     /*
                     $typeName = M('salon_itemtype')->where(array('typeid' => $value))->getField('typename');
                      */
                     $typeName = D('SalonItemType')->getTypeName($value);
                 }          
                 $vUseItemTypesArray[]= $typeName;
            }
            $vUseItemTypesValue = implode(',',$vUseItemTypesArray);
            $diff=array_diff($allItemTypes, $itemIdArray);
            //避免报错
            if (!empty($diff)) {
                $limit.="指定{$vUseItemTypesValue}项目可用;";
            }  
        }
        if(empty($limit)){
            $limit = "无使用限制";
        }
           
       $resVoucherInfo = array(
           'vId' => (string)$vId,
           'vUseMoney' => (string)$vUseMoney,
           'vcTitle' => $vcTitle,
           'vUseStart' => date("Y-m-d",$vUseStart),
           'vUseEnd' => date("Y-m-d",$vUseEnd),
           'vUseNeedMoney' => $vUseNeedMoney,
           'vUseItemTypesMsg' => $vUseItemTypesValue,
           'vUseLimitTypesValueMsg' => $vUseLimitTypesValue,
           'limit' => $limit,
       );
       return $resVoucherInfo;
    }
    
    //根据订单号查询订单金额，查询代金券金额，返回能抵用金额
    public function getCanUseVoucherMoney($orderSn){
        
        /*
        $orderPrice = M('order')->where(array('ordersn' => $orderSn))->getField('priceall');
        $voucherPrice = M('voucher')->where(array('vOrderSn' => $orderSn))->getField('vUseMoney');
         */
        $orderPrice = D('Order')->getOrderBySn($orderSn)['priceall'];
        $voucherPrice = $this->getVocherByOrdersn($orderSn)['vUseMoney'];
        if($voucherPrice){
            if($orderPrice >= $voucherPrice){
                return $voucherPrice;
            }else{
                return $orderPrice;
            }
        }else{
            return 0;
        }
    }
    
    //判断代金券是否符合使用条件
    public function isVidCanBeUsed($vId,$userId,$orderSn){
        return 1;
        //获取是否是首单
        $res = $this->IsFirstOrder($userId);
        if($res){
            $IsFirst = 2;
        }else{
            $IsFirst = 1;
        }
        $orderVoucherInfo = $this->getOrderVoucherInfo($orderSn);
        
        if(!$orderVoucherInfo){
            return false;
        }
        // 根据用户id + 可使用项目类别 + 使用限制类型 + 需满足金额 + 可使用结束时间 + 代金券的状态   
        
        //将代金券的信息和当前信息作对比，看是否符合条件
        $time =time();
        /*
        $where[] = "vId = {$vId}";
        $where[] = "vUserId = {$userId}";
        //$where[] = "find_in_set({$orderVoucherInfo['typeId']},vUseItemTypes)";
        $where[] = "vUseNeedMoney <= {$orderVoucherInfo['itemPrice']} ";
        $where [] = "{$time} BETWEEN IF(vUseStart=0,'1400000000',vUseStart) and IF(vUseEnd=0,'1600000000',vUseEnd)";
        $where[] = "vStatus = 1";
        //选出了代金券是否符合条件
        $voucherInfo = $this->where(implode(' AND ',$where))->find();
         */
        
        $voucher = $this->getVoucherById($vId);
        if(empty($voucher) || $voucher['vUserId'] != $userId || $voucher['vUserNeedMoney'] > $orderVoucherInfo['itemPrice']
        || $voucher['vStatus'] != 1 || ($voucher['vUseStart']>0 && $time<$voucher['vUseStart']) || ($voucher['vUseEnd']>0 && $time>$voucher['vUseEnd']))
            $voucherInfo = null;
        else
            $voucherInfo = $voucher;
        
        if($voucherInfo){
            if(empty($voucherInfo['vUseItemTypes'])){
                if(strpos($voucherInfo['vUseLimitTypes'],"2") === false){
                    return 1;
                }else{              
                    if($IsFirst == 2){
                       return 1;
                    }else{
                        return 0;
                    }
                }
            }else{
                if(strpos($voucherInfo['vUseItemTypes'],$orderVoucherInfo['typeId']) !== false){
                    if(strpos($voucherInfo['vUseLimitTypes'],"2") === false){
                        return 1;
                    }else{              
                        if($IsFirst == 2){
                           return 1;
                        }else{
                            return 0;
                        }
                    }
                }
                
            }
            
        }else{
            return 0;
        }       
        
    }
    //将代金券和订单号绑定
    /**
     * @deprecated since version thrift
     * @param type $vId
     * @param type $userId
     * @param type $orderSn
     */
    public function bindVoucherAndOrder($vId,$userId,$orderSn){
        /*
        //通过订单号，获取店铺id，店铺名，项目id ,项目名
        $needorderInfo = D('order')->getOrderAndSalonInfo($orderSn);
        $data = array(
            'vOrderSn' => $orderSn,
            'vSalonId' => $needorderInfo['salonId'],
            'vSalonName' => $needorderInfo['salonName'],
            'vItemId' => $needorderInfo['itemId'],
            'vItemName' => $needorderInfo['itemName'],
        );
        $where = array(
            'vId' => $vId,
            'vUserId' => $userId,      
        );
        $bindRes = $this->where($where)->save($data);
        return $bindRes;
         */
        
    }
    
    //已经有了绑定关系，验证一下是否存在绑定
    public function isVidBind($vId){
        /*
        $vOrderSn = M('voucher')->where(array('vId' => $vId))->getField('vOrderSn');
        if($vOrderSn){
            return $vOrderSn;
        }else{
            return false;
        }
         */
        $voucher = $this->getVoucherById($vId);
        if(empty($voucher) || $voucher['vStatus'] != 1)
            return false;
        return $voucher['vOrderSn'];
    }
    
    //增加一条动态记录到cm_voucher_trend表
    public function addVoucherTrend($vId,$userId,$orderSn,$vStatus){
        //通过vId 获取vSn
        /*
        $vSn = M('voucher')->where(array('vId' => $vId))->getField('vSn');
        $data = array(
            'vId' => $vId,
            'vSn' => $vSn,
            'vUserId' => $userId,
            'vOrderSn' => $orderSn,
            'vAddTime' => time(),
            'vStatus' => $vStatus,
        );
        try {
            M('voucher_trend')->add($data);
        } catch (Exception $e) {
            Log::write("订单为{$orderSn}的代金券动态数据添加失败:",$e->getMessage());
        }
         */
        $voucher = $this->getVoucherById($vId);
        D('VoucherTrend')->addTrend($vId, $voucher['vSn'], $userId, $orderSn, $vStatus);
    }
    //判断当前动态记录表是否存在一样的记录，同样的不需要添加
    public function isSameVoucherTrend($vId,$orderSn){
        /*
        $where = array(
            'vId' => $vId,
            'vOrderSn' => $orderSn,
            'vStatus' => 1,
        );
        $res = M('voucher_trend')->where($where)->count();
        return $res;
         */
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'getVoucherTrends', array($vId, $orderSn));
    }
    
    public function getVoucherById($vId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'getVocherById', array($vId));
    }
    
    public function getVocherByOrdersn($orderSn)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'getVocherByOrdersn', array($orderSn));
    }
    
    public function unbindOrder($vId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'unbindOrder', array($vId));
    }
    
    public function bindOrder($vId, $orderSn, $salonId, $salonName, $itemId, $itemName)
    {
        $param = new \cn\choumei\thriftserver\service\stub\gen\BindOrderParam();
        $param->vId = $vId;
        $param->orderSn = $orderSn;
        $param->salonId = $salonId;
        $param->salonName = $salonName;
        $param->itemId = $itemId;
        $param->itemName = $itemName;
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'bindOrder', array($param));
    }
    
    public function getVouchersByUserId($userId, $status = 0, $vcId = 0)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'getVoucherByIds', array($userId, $status, $vcId));
    }
    
    /**
     * 支持多个券更新，vid要传入数组！
     * @param type $vIds 
     * @param type $status
     * @param type $userId
     * @return type
     */
    public function updateVoucherStatus($vIds, $status, $userId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('voucher-center', 'updateVoucherStatus', array($vIds, $status, $userId));
    }
    
    
     /**
   *获取过期提醒的代金券
   */
    public function getVouchersExpiring($startTime,$endTime,$page,$pageSize)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('voucher-center', 'getVouchersExpiring', array($startTime,$endTime,$page,$pageSize));
        return $return;
    }
}