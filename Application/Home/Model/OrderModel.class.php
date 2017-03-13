<?php
/**
 * 订单处理类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class OrderModel extends BaseModel {

   const saleStartTime = '09:00';
   const saleEndTime = '14:00';
   
    // 验证项目特价时间是否过期     1 已经过期 2 还未开始  3 活动进行中
    public function saleTimeExp(){
        $saleTime = $this->getCacheSaleTime();
        $saleStartTime = $this->formatTimeStr( $saleTime[0] );
        $saleEndTime = $this->formatTimeStr( $saleTime[1] );
        $serviceTimestamp = time();

        $diffStart = $serviceTimestamp - $saleStartTime;
        $diffEnd = $serviceTimestamp - $saleEndTime;
        // 已经过期
        if( $diffEnd>0 )
            return 1;
        // 还未开始
        if( $diffStart<0 )
            return 2;
        // 活动进行中
        return 3;
    }
    // 格式化项目特价时间为时间戳形式 如 09:00 -> 当天零9点（2015-06-25 09:000） -> 时间戳
    private function formatTimeStr( $str ){
        $timeStr = date('Y-m-d ') . $str;
        return strtotime($timeStr);
    }
    // 获取程序中写死的时间
    public function getCacheSaleTime(){
		$saleResTime = array( self::saleStartTime , self::saleEndTime );
		if( ENVIRONMENT != 'prod' && !empty($this->param['saleTimeStart']) && !empty($this->param['saleTimeEnd'])){
			$saleResTime = array($this->param['saleTimeStart'],$this->param['saleTimeEnd']);
		}
        return $saleResTime;
    }
    /**
     * 获取订单信息数量
     * @param $where
     * @return mixed
     * @deprecated since version thrift_150709
     */
    public function getCount($where) {

        return $this->where($where)->count();
         
    }


    /**
     * 获取项目的价格信息
     * @param $userId
     * @param $itemId
     * @param $salonNormsId
     * @return bool|mixed
     */
    public function getOrderPrice($userId,$itemId,$salonNormsId){
        if($salonNormsId) { //有规则
            /*
            $where['itemid']=$itemId;
            $where['salon_norms.salon_norms_id']=$salonNormsId;

            $field='salon_item_format_id,price,price_dis,price_group';
            $priceInfo = D('SalonNormsPriceView')->field($field)->where($where)->find(); //价格
            if(!$priceInfo){
                return false;
            }
             */
            $priceInfoData = D('SalonNormsPriceView')->getItemPriceFormats($itemId);
            $priceInfo = null;
            foreach($priceInfoData as $each)
            {
                if($salonNormsId != $each['salonNormsId'])
                    continue;
                $priceInfo = array(
                    'salon_item_format_id' => $each['salonItemFormatId'],
                    'price' => $each['price'], 
                    'price_dis' => $each['priceDis'],
                    'price_group' => $each['priceGroup'],
                );
                break;
            }
            if(!$priceInfo)
                return false;
            
            $priceInfo['normsStr'] = D('SalonItemFormatsView')->getFormatStr($priceInfo['salon_item_format_id']); //项目规则
            $priceInfo['hasrule']=1;

            //unset($priceInfo['salon_item_format_id']);
        } else { //无规则
            /*
            $where['itemid']=$itemId;
            $field='salon_norms_id,price,price_dis,price_group';
            $priceInfo = M('salon_item_format_price')->field($field)->where($where)->order('salon_item_format_price_id desc')->find();

            if(!$priceInfo){
                return false;
            }
             */
            $priceInfoDatas = D('SalonNormsPriceView')->getItemPriceFormats($itemId);
            if(empty($priceInfoDatas) || count($priceInfoDatas) != 1)
                return false;
            $priceInfoData = array_shift($priceInfoDatas);
            $priceInfo = array(
                    'salon_norms_id' => $priceInfoData['salonNormsId'],
                    'price' => $priceInfoData['price'], 
                    'price_dis' => $priceInfoData['priceDis'],
                    'price_group' => $priceInfoData['priceGroup'],
                );
            $priceInfo['hasrule']=0;
            $priceInfo['normsStr']='无规格';
            unset($priceInfo['salon_norms_id']);
        }
        $priceInfo['companyId']=0; //集团ID搞出来
        $priceInfo['isGroupPrice']=0;//是否使用过集团价
        $companyData=D('User')->getUserCompanyStatus($userId);

        if($companyData['companyStatus']==1 && SOURCE_TYPE!=3){
            $priceInfo['companyId']=$companyData['companyId'];

            if($priceInfo['price_group']>0){
                $priceInfo['price_dis']=$priceInfo['price_group'];
                $priceInfo['isGroupPrice']=1;
            }
        }
        unset($priceInfo['price_group']);
        /*print_r($priceInfo);
        die();*/
        return $priceInfo;
    }


    /**
     * 获取订单信息 通过订单sn和用户id
     * @deprecated since version thrift_150709
     * @param $ordersn
     * @param $user_id
     * @return array|bool
     */
    public function getOrderAndOrderItemBySnAndUserId($ordersn,$user_id) {

        /*
        $orderW['ordersn']=$ordersn;
        $orderW['user_id']=$user_id;
        $order = $this->field('orderid,ordersn,salonid,user_id,num,priceall,priceall_ori,add_time')->where($orderW)->find();
        if(!$order) return false;
        
        /*
        $orderItem = M('order_item')->field('salonid,itemid,itemname,num,price_dis,priceall,end_time,normsStr,salonNormsId')->where("orderid = {$order['orderid']}")->find(); //订单项目
         */
        /*
        $orderItem = D('OrderItem')->getOrderItemByOrderId($order['orderid']);
        if(!$orderItem) return false;
         */
        
        /*
        $salon = M('salon')->field('salonname')->find($order['salonid']);
        if(!$salon) return false;
         */
        /*
        $salonname = D('Salon')->getSalonNameById($order['salonid']);
        if(empty($salonname)) return false;
        $salon['salonname'] = $salonname;

        return array('order'=>$order,'order_item'=>$orderItem,'salon'=>$salon);
         */
    }


    /**
     * 通过订单sn和用户id 获取订单信息 (未付款订单调用)
     * @deprecated since version thrift_150709
     * @param $ordersn
     * @param $user_id
     * @return array|bool
     */
    public function getOrderOrderItemInfo($ordersn,$user_id) {

        $orderW['ordersn']=$ordersn;
        $orderW['user_id']=$user_id;
        $orderW['ispay']=1;
        /*
        $order = $this->field('orderid,ordersn,salonid,priceall,user_id')->where($orderW)->find();
         */
        $order = $this->getOrderbyOrderSn($ordersn);
        if(!$order) return false;

        /*
        $orderItem = M('order_item')->field('order_item_id,ordersn,salonid,itemid,num,price_dis,end_time,salonNormsId')->where("orderid = {$order['orderid']}")->find(); //订单项目
         */
        $orderItem = D('OrderItem')->getOrderItemByOrderId($order['orderid']);
        if(!$orderItem) return false;

        return array('order'=>$order,'order_item'=>$orderItem);
    }


/************************************* 下单相关 *****************************************************************/

    /**
     * 获取订单编号
     * @param $source
     * @return string
     */
    private function getordersn($source) {
        $pre = substr(time(),2);

        $end = '';
        for($i=0;$i<3;$i++) {
            $end .= rand(0,9);
        }

        $code = $pre.$source.$end;
        /*
        $where = array('ordersn'=>$code);
        $count = $this->getCount($where);
        if($count) {
            return $this->getordersn($source);
        } else {
            return $code;
        }
        */
        $order = $this->getOrderbySn($code);
        if(empty($order))
            return $code;
        
        return $this->getordersn($source);
    }


    /**
     * 普通下单
     * @param $userId           用户id
     * @param $itemid           项目id
     * @param $salonNormsId     规格id
     * @return int
     */
    public function addOrder($userId,$itemid,$salonNormsId){
        $num=1;  //每次购买数量  这里设置为1
        if(!$userId || !$itemid){
            return 1;
        }
        return $this->createOrder($userId,$itemid,$salonNormsId,$num);
    }


    /**
     * 购物车下单
     * @param $userId           用户id
     * @param $shopcartsn       购物车号
     * @param $itemid           项目id
     * @param $salonNormsId     规格id
     * @param $ckNum            数量
     * @return int
     */
    public function shopcartAddOrder($userId,$shopcartsn,$itemid,$salonNormsId,$ckNum){

        if(!$userId || !$shopcartsn || !$itemid){
            return 1;
        }
        return $this->createOrder($userId,$itemid,$salonNormsId,$ckNum,$shopcartsn);
    }


    /**
     * 生成订单
     * @param $userId           用户id
     * @param $itemid           项目id
     * @param $salonNormsId     规格id
     * @param $ckNum            数量
     * @param $shopcartsn       购物车号（购物车下单时传值）
     * @return int
     */
    private function createOrder($userId,$itemid,$salonNormsId,$ckNum,$shopcartsn=''){

        $sourcetype = getSourcetype();
        !$sourcetype && $sourcetype=1; //测试开启
        if(!in_array($sourcetype,array(1,2,3))){
            //return 1;
        }

        $num=1;  //每次购买数量  这里设置为1
        if(!$itemid){
            $this->error=1;
            return false;
        }

        /*
        $itemInfo = M('salon_item')->field('typeid,itemid,salonid,itemname,exp_time,total_rep,sold,desc,useLimit,item_type,innage,repertory')->where('status = 1 and itemid = '.$itemid)->find(); //项目
        if(!$itemInfo){
            $this->error=2003;
            return false;
        }
        */
        $item = D('SalonItem')->getItemById($itemid);
        if(empty($item) || $item['status'] != 1)
        {
            $this->error = 2003;
            return false;
        }
        $itemInfo = array(
            'typeid' => $item['typeid'],
            'itemid' => $item['itemid'],
            'salonid' => $item['salonid'],
            'itemname' => $item['itemname'],
            'exp_time' => $item['expTime'],
            'total_rep' => $item['totalRep'],
            'sold' => $item['sold'],
            'desc' => $item['desc'],
            'useLimit' => $item['useLimit'],
            'item_type' => $item['itemType'],
            'innage' => $item['innage'],
            'repertory' => $item['repertory'],
        );

        //验证项目的信息
        $itemCkRs=$this->valiItemInfo($itemInfo,$ckNum,$userId);
        if($itemCkRs){
            $this->error=$itemCkRs;
            return false;
        }

        /*if($salonNormsId) { //有规则
            $current = D('SalonNormsPriceView')->field('salon_item_format_id,price,price_dis')->where("itemid={$itemid} and salon_norms.salon_norms_id = '{$salonNormsId}'")->find(); //价格
            //echo D('SalonNormsPriceView')->_sql();
            if(!$current){
                $this->error=2001;
                return false;
            }
        } else { //无规则
            $where = array('itemid'=>$itemid);
            $salon_item_format_price = M('salon_item_format_price')->field('salon_norms_id,price,price_dis')->where($where)->order('salon_item_format_price_id desc')->find();
            $current['price'] = $salon_item_format_price['price'];
            $current['price_dis'] = $salon_item_format_price['price_dis'];
        }*/
        $priceInfo=$this->getOrderPrice($userId,$itemid,$salonNormsId);
        $priceall = $priceInfo['price_dis']*$num;
        $priceall_ori = $priceInfo['price']*$num;
        $normsStr = $priceInfo['normsStr'];
        if($itemInfo['typeid']!=6){
            if(!$priceall){
                $this->error=10;
                return false;
            }
        }
        $source = $sourcetype.'1';
        //生成订单
        $order = array(
            'ordersn' => $this->getordersn($source), //设备来源；线上线下
            'shopcartsn' => $shopcartsn,
            'salonid' => $itemInfo['salonid'],
            'salonid' => $itemInfo['salonid'],
            'user_id' => $userId,
            'num'          => 1,
            'priceall'     => $priceall,
            'priceall_ori' => $priceall_ori,
            'actuallyPay' => $priceall, //实付金额为订单金额，之后在绑定代金券的时候再更新此金额
            'add_time'     => time(),
            'companyId'     => $priceInfo['companyId'],
            'isCompanyPrice'     => $priceInfo['isGroupPrice'],
        );
        /*
        $orderid = $this->add($order);
        */
        $param = new \cn\choumei\thriftserver\service\stub\gen\OrderParam();
        $param->addTime = time();	    
        $param->companyId = $priceInfo['companyId'];	    
        $param->isCompanyPrice = $priceInfo['isGroupPrice'];          
        $param->num = 1;    
        $param->ordersn = $order['ordersn'];           
        $param->priceall = $priceall;         
        $param->priceallOri = $priceall_ori;      
        $param->salonId = $itemInfo['salonid'];         
        $param->shopcartsn = $shopcartsn;         
        $param->userId = $userId; 
        $param->actuallyPay = $priceall;
        $thrift = D('ThriftHelper');
        $orderid =  $thrift->request('trade-center', 'addOrder', array($param));   
        //echo $this->_sql();
        if(!$orderid){
            $this->error=10;
            return false;
        }


        //订单项目
        /*
        $orderItemData = array(
            'orderid'  => $orderid,
            'ordersn'  => $order['ordersn'],
            'itemid'   => $itemid,
            'user_id'  => $userId,
            'salonid'  => $itemInfo['salonid'],
            'itemname' => $itemInfo['itemname'],
            'num'      => $num,
            'price_dis' => $priceInfo['price_dis'],
            'priceall'  => $priceall,
            'priceall_ori' => $priceall_ori,
            'extra'        => $priceInfo['salon_item_format_id'],
            'normsStr'     => $normsStr,
            'end_time'     => $itemInfo['exp_time'],
            'service_detail' => $itemInfo['desc'],
            'useLimit' => $itemInfo['useLimit'],
            'salonNormsId' => $salonNormsId
        );
        $affectid = M('order_item')->add($orderItemData);
         */
        $affectid = D('OrderItem')->addOrderItem($orderid, $order['ordersn'], $itemid, $userId, $itemInfo['salonid'], $itemInfo['itemname'], $num, $priceInfo['price_dis'],
                $priceall, $priceall_ori, $priceInfo['salon_item_format_id']?: 0, $normsStr, $itemInfo['exp_time'], $itemInfo['desc'], $itemInfo['useLimit'], $salonNormsId);
        if(!$affectid){
            $this->error=10;
            return false;
        }

        //M('salon_item')->where("itemid = $itemid")->setInc('salenum',$num); //更新购买数量
        //推荐码
        try {
            D('RecommendCodeOrder')->toRecordItOnOrder($order['ordersn']);
        } catch (Exception $e) {
            $this->show_log($e->getMessage());
        }

        //返回订单成功的序列号
        return $order['ordersn'];
    }


    /**
     * 余额支付 后续处理(流水的写入)
     * @param $userId           用户id
     * @param $order            订单信息
     * @param $orderItem       订单项目信息
     * @return int
     */
    public function todoMoneyPay($userDeviceInfo,$userId,$order,$orderItem,$vUseMoney = 0){
        if(!$userId || !$order || !$orderItem){
            $this->error=1000;
            return false;
        }
        //余额支付或代金券支付的，判断是web还是app
        if(strtolower($userDeviceInfo['type']) == 'wechat'){
            $device = 2;
        }else{
            $device = 1;
        }
        //如果是普通下单，则$ckNum = 1
        $ckNum = 1;
        
        /*
        //支付时，也对项目进行验证
        $itemInfo = M('salon_item')->field('typeid,itemid,salonid,itemname,exp_time,total_rep,sold,desc,useLimit,item_type,innage,repertory')->where('status = 1 and itemid = '.$orderItem['itemid'])->find(); //项目
        if(!$itemInfo){
            $this->error=2003;
            return false;
        }
        */
        
        $item = D('SalonItem')->getItemById($orderItem['itemid']);
        if(empty($item) || $item['status'] != 1)
        {
            $this->error = 2003;
            return false;
        }
        $itemInfo = array(
            'typeid' => $item['typeid'],
            'itemid' => $item['itemid'],
            'salonid' => $item['salonid'],
            'itemname' => $item['itemname'],
            'exp_time' => $item['expTime'],
            'total_rep' => $item['totalRep'],
            'sold' => $item['sold'],
            'desc' => $item['desc'],
            'useLimit' => $item['useLimit'],
            'item_type' => $item['itemType'],
            'innage' => $item['innage'],
            'repertory' => $item['repertory'],
        );

        //验证项目的信息
        $itemCkRs=$this->valiItemInfo($itemInfo,$ckNum,$userId);
        if($itemCkRs){
            $this->error=$itemCkRs;
            return false;
        }
        
        
        $ordersn=$order['ordersn'];
        
        //获取最新项目价格
        $newPrice=$this->updateOrderOnPriceChanged($order,$orderItem);
        if(!$newPrice){
            return false;
        }

        $order['priceall'] = $newPrice;
        $orderItem['price_dis'] = $newPrice;
        
        $ticket_no = D('OrderTicket')->getticketno(); //臭美劵
        //先使用代金券
        if($vUseMoney > 0)
            D('Fundflow')->addFundflow($ordersn, $ticket_no, $userId, $vUseMoney, 9, $order['salonid'], 2);
        $needPayPrice = $order['priceall'] - $vUseMoney ;
        //如果需要余额支付
        if($needPayPrice > 0){
            /*
            $UserObj = M('user');
            $user = $UserObj->field('costpwd,money')->find($userId);
             */
            $userInfo = D('User')->getUserById($userId);
            $user = array(
                'money' => $userInfo['money'],
                'costpwd' => $userInfo['costpwd'],
            );
            if($user['money']+$vUseMoney < $order['priceall']){
                $this->error=1001;
                return false;
            }
            //修改支持事务
            /*
            $userW['user_id']=$userId;
            $userW['money']=array('EGT',$needPayPrice);
            $affectid = $UserObj->where($userW)->setDec('money',$needPayPrice);
             */
            //更新用户余额
            $affectid = D('User')->updateUserMoney($userId, 0-$needPayPrice);
            if($affectid === false) {
                $this->error=1003;
                return false;
            }
            //扣用户余额后写账户余额支付流水
            $fund_affectid = D('Fundflow')->addFundflow($ordersn, $ticket_no, $userId, $needPayPrice, 4, $order['salonid'], 2);
            if(!$fund_affectid) {
                $this->error=1005;
                return false;
            }
        }
        /*
        $UserObj = M('user');
        $user = $UserObj->field('costpwd,money')->find($userId);
        if($user['money']<$order['priceall']){
            $this->error=1001;
            return false;
        }

        $error = 10; //失败的提醒

        //修改支持事务
        $userW['user_id']=$userId;
        $userW['money']=array('EGT',$order['priceall']);
        $affectid = $UserObj->where($userW)->setDec('money',$order['priceall']);
        if(!$affectid) {
            $this->error=1003;
            return false;
        }
        */
        /*
        //修改支付状态
        $order_affectid = $this->where("ordersn='$ordersn'")->save(array('ispay'=>2,'pay_time'=>time())); //修改支付状态
         */
        $order_affectid = $this->updateOrderIsPayByOrderSn($ordersn, 2);
        if(!$order_affectid) {
            $this->error=2202;
            return false;
        }
        
        /*
         * 调整生成券号、添加流水的位置，往上找
        $ticket_no = D('OrderTicket')->getticketno(); //臭美劵
         */
        /*
        $fund_affectid = $this->fundflowAdd($ticket_no,$userId,$ordersn,$orderItem,$order,$vUseMoney);
        */
        /*
        $fund_affectid = D('Fundflow')->addFundflow($ordersn, $ticket_no, $userId, $orderItem['price_dis'], 4, $order['salonid'], 2);
        
        if(!$fund_affectid) {
            $this->error=$error;
            return false;
        }
         */

        $ticket_affectid = $this->ticketAdd($orderItem,$userId,$ticket_no); //生成臭美劵
        if(!$ticket_affectid) {
            return false;
        }

        //统计项目销售数量
        /*
        $itemWhere['itemid']=$orderItem['itemid'];
        $salonitemRs=M('salon_item')->where($itemWhere)->setInc('sold',$orderItem['num']);
        if(!$salonitemRs) {
            $this->error=$error;
            return false;
        }
        //限时特价时
        if($itemInfo['item_type']==2){
            $upInnageRs=M('salon_item')->where($itemWhere)->setInc('innage',$orderItem['num']);
            if(!$upInnageRs) {
                $this->error=$error;
                return false;
            }
        }
        */
	
        if($itemInfo['item_type'] == 2)
            $innage = 1;
        else
            $innage = 0;
        $salonitemRs = D('SalonItem')->updateItemRepertory($orderItem['itemid'], $orderItem['num'], $innage);
        if(!$salonitemRs) {
            $this->error=2009;
            return false;
        }
         
        if($needPayPrice > 0){
            //把数据写入支付日志表中
            /*
            $data = array();
            $data["tn"] = 10000;  //流水号  固定的10000代表由余额支付生成
            $data["ordersn"] 	= $ordersn;
            $data["add_time"]	= time();
            $data["payid"]		= 4; //支付类型   1：支付宝  2：微信  3：银联  4:余额支付
            $data["device"]		= 1; // 1:app支持过来的
            $data["amount"] 	= $needPayPrice; //支付的总金额
        
            M("payment_log")->add($data);   
            */
            D('PaymentLog')->addPaymentLog(10000, $ordersn, $needPayPrice, $device, 1, 4);             
        }
        if($vUseMoney > 0){
            //更新代金券状态
            /*
            $updateVoucherRes = M('voucher')->where("vOrderSn='$ordersn'")->save(array('vStatus'=>2,'vUseTime'=>time())); //修改代金券使用状态
             */
            $orderVoucher = D('Voucher')->getVocherByOrdersn($ordersn);
            if(empty($orderVoucher))
                $updateVoucherRes = false;
            else
                $updateVoucherRes = D('Voucher')->updateVoucherStatus(array($orderVoucher['vId']), 2, $userId);
            
            if(!$updateVoucherRes){
                \Think\Log::write("用户代金券状态更新失败,userId:{$userId}, vId:{$orderVoucher['vId']}");
            }  
            //如果有代金券的，也要写支付日志
            /*
            $data2 = array();
            $data2["tn"] = 10001;  //流水号  固定的10001代表由代金券支付生成
            $data2["ordersn"] 	= $ordersn;
            $data2["add_time"]	= time();
            $data2["payid"]		= 5; //支付类型   1：支付宝  2：微信  3：银联  4:余额支付 5 代金券
            $data2["device"]		= 1; // 1:app支持过来的
            $data2["amount"] 	= $vUseMoney; //代金券支付的金额
        
            M("payment_log")->add($data2);
            */
            D('PaymentLog')->addPaymentLog(10001, $ordersn, $needPayPrice, $device, 1, 5);
            
            //根据订单号，找出vId
            /*
            $vId = M('voucher')->where(array('vOrderSn' => $ordersn))->getField('vId');
             */
            $vId = $orderVoucher['vId'];
            //增加一条代金券动态记录
            $voucherObj = D('Voucher');
            $vStatus = 2;
            $voucherObj->addVoucherTrend($vId,$userId,$ordersn,$vStatus);
        } 
        $this->sendToCrm($ordersn); //同步crm订单
        return true;
    }

    /**
     * 代金券和余额支付 后续处理(流水的写入)
     * @param $userId           用户id
     * @param $order            订单信息
     * @param $orderItem       订单项目信息
     * @return int
     */
    /***
    public function todoVoucherMoneyPay($userId,$order,$orderItem,$vId,$vUseMoney){
        if(!$userId || !$order || !$orderItem){
            $this->error=1000;
            return false;
        }
        //如果是普通下单，则$ckNum = 1
        $ckNum = 1;
        
        //支付时，也对项目进行验证
        $itemInfo = M('salon_item')->field('typeid,itemid,salonid,itemname,exp_time,total_rep,sold,desc,useLimit,item_type,innage,repertory')->where('status = 1 and itemid = '.$orderItem['itemid'])->find(); //项目
        if(!$itemInfo){
            $this->error=2003;
            return false;
        }

        //验证项目的信息
        $itemCkRs=$this->valiItemInfo($itemInfo,$ckNum,$userId);
        if($itemCkRs){
            $this->error=$itemCkRs;
            return false;
        }
        
        
        $ordersn=$order['ordersn'];
        
        //获取最新项目价格
        $newPrice=$this->updateOrderOnPriceChanged($order,$orderItem);
        if(!$newPrice){
            return false;
        }

        $order['priceall'] = $newPrice;
        $orderItem['price_dis'] = $newPrice;

        $needPayPrice = $order['priceall'] - $vUseMoney ;
        //如果需要余额支付
        if($needPayPrice > 0){
            $UserObj = M('user');
            $user = $UserObj->field('costpwd,money')->find($userId);
            if($user['money']+$vUseMoney < $order['priceall']){
                $this->error=1001;
                return false;
            }
            //修改支持事务
            $userW['user_id']=$userId;
            $userW['money']=array('EGT',$needPayPrice);
            $affectid = $UserObj->where($userW)->setDec('money',$needPayPrice);
            if($affectid === false) {
                $this->error=1003;
                return false;
            }
        }
        
        $error = 10; //失败的提醒
        $order_affectid = $this->where("ordersn='$ordersn'")->save(array('ispay'=>2,'pay_time'=>time())); //修改支付状态
        if(!$order_affectid) {
            $this->error=$error;
            return false;
        }

        $ticket_no = D('OrderTicket')->getticketno(); //臭美劵
        $fund_affectid = $this->fundflowAdd($ticket_no,$userId,$ordersn,$orderItem,$order);
        if(!$fund_affectid) {
            $this->error=$error;
            return false;
        }

        $ticket_affectid = $this->ticketAdd($orderItem,$userId,$ticket_no); //生成臭美劵
        if(!$ticket_affectid) {
            return false;
        }

        //统计项目销售数量
        $itemWhere['itemid']=$orderItem['itemid'];
        $salonitemRs=M('salon_item')->where($itemWhere)->setInc('sold',$orderItem['num']);
        if(!$salonitemRs) {
            $this->error=$error;
            return false;
        }
        //限时特价时
        if($itemInfo['item_type']==2){
            $upInnageRs=M('salon_item')->where($itemWhere)->setInc('innage',$orderItem['num']);
            if(!$upInnageRs) {
                $this->error=$error;
                return false;
            }
        }
        
        //更新代金券状态
        $updateVoucherRes = M('voucher')->where("vOrderSn='$ordersn'")->save(array('vStatus'=>2,'vUseTime'=>time())); //修改代金券使用状态
        if(!$updateVoucherRes){
            Log::write("用户代金券状态更新失败,userId:".$userId);
        }      
        if($needPayPrice > 0){
            //把数据写入支付日志表中
            $data = array();
            $data["tn"] = 10000;  //流水号  固定的10000代表由余额支付生成
            $data["ordersn"] 	= $ordersn;
            $data["add_time"]	= time();
            $data["payid"]		= 4; //支付类型   1：支付宝  2：微信  3：银联  4:余额支付
            $data["device"]		= 1; // 1:app支持过来的
            $data["amount"] 	= $needPayPrice; //支付的总金额

            M("payment_log")->add($data);                
        }
        //如果有代金券的，也要写支付日志
        $data2 = array();
        $data2["tn"] = 10001;  //流水号  固定的10001代表由代金券支付生成
        $data2["ordersn"] 	= $ordersn;
        $data2["add_time"]	= time();
        $data2["payid"]		= 5; //支付类型   1：支付宝  2：微信  3：银联  4:余额支付 5 代金券
        $data2["device"]		= 1; // 1:app支持过来的
        $data2["amount"] 	= $vUseMoney; //代金券支付的金额

        M("payment_log")->add($data2);
        
        
        //echo 'sb';
        $this->sendToCrm($ordersn); //同步crm订单
        return true;
    }
    **/
    
    /**
     * 如果项目价格有更改 订单的价格也要更新
     * @param $orderInfo
     * @param $orderItemInfo
     * @return bool
     */
    public function updateOrderOnPriceChanged(&$orderInfo,&$orderItemInfo){

        $priceInfo = $this->getOrderPrice($orderItemInfo['user_id'],$orderItemInfo['itemid'],$orderItemInfo['salonNormsId']);

        if($orderInfo['priceall'] != $priceInfo['price_dis'] || $orderItemInfo['price_dis'] != $priceInfo['price_dis']){
            /*
            $where['ordersn']=$orderInfo['ordersn'];
            $oRs = $this->where($where)->setField('priceall',$priceInfo['price_dis']);
             */
            $oRs = $this->updateOrderPrice($orderInfo['ordersn'], $priceInfo['price_dis']);
            if(!$oRs){
                $this->error=2006;
                return false;
            }

            $data['price_dis']=$priceInfo['price_dis'];
            $data['priceall']=$priceInfo['price_dis'];
            /**
              $oiRs = M('order_item')->where($where)->setField($data);
             */
            $oiRs = D('OrderItem')->updateOrderItemPrice($orderInfo['ordersn'], $priceInfo['price_dis'], $priceInfo['price_dis']);
            if(!$oiRs){
                $this->error=2006;
                return false;
            }
        }
        return $priceInfo['price_dis'];
    }
    
    private function updateOrderPrice($orderSn, $priceall)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateOrderPrice', array($orderSn, $priceall));
    }


    /**
     * 写入臭美券
     * @param $orderItemInfo
     * @param $userId
     * @param $ticket_no
     * @return int
     */
    public function ticketAdd($orderItemInfo,$userId,$ticket_no) {

        if(!$orderItemInfo || !$userId || !$ticket_no){
            $this->error=1000;
            return false;
        }
        if(!$orderItemInfo['ordersn'] || !$orderItemInfo['order_item_id'] || !$orderItemInfo['salonid']){
            $this->error=1000;
            return false;
        }

        /*
        $ticket = array(
            'otOrdersn' => $orderItemInfo['ordersn'],
            'order_item_id' => $orderItemInfo['order_item_id'],
            'user_id'  => $userId,
            'ticketno' => $ticket_no,
            'add_time' => time(),
            'end_time' => $orderItemInfo['end_time'],
            'otSalonId' => $orderItemInfo['salonid'],
        );
        $ticketRs = M('order_ticket')->add($ticket);
         */
        $ticketRs = D('OrderTicket')->addOrderTicket($orderItemInfo['order_item_id'], $orderItemInfo['ordersn'], $userId, $ticket_no, $orderItemInfo['end_time']);
        if(!$ticketRs){
            $this->error=10;
            return false;
        }

        //劵动态
        /*
        $data = array(
            'ordersn'  => $orderItemInfo['ordersn'],
            'ticketno' => $ticket_no,
            'add_time' => time(),
            'status'   => 2, //状态：2未使用，4使用完成，6申请退款，7退款完成
        );
        $ticketRs = M('order_ticket_trends')->add($data);
         */
        $ticketRs = D('OrderTicketTrends')->addOrderTicketTrends($orderItemInfo['ordersn'], $ticket_no, 2, '');
        if(!$ticketRs){
            $this->error=10;
            return false;
        }

        return true;
    }


    /**
     * 流水的写入记录  (余额支付)
     * @param $ticket_no        券号
     * @param $userId           用户编号
     * @param $ordersn          订单号
     * @param $orderItemInfo    订单项目信息
     * @param $order
     * @return int
     * @deprecated since version thrift_150709
     */
    public function fundflowAdd($ticket_no,$userId,$ordersn,$orderItemInfo,$order,$vUseMoney = 0) {

        /*
        if(!$ticket_no || !$userId || !$ordersn || !$orderItemInfo || !$order) return;

        $leftmoney   = $orderItemInfo['price_dis'];
        $user_coupon = M('user')->field('couponmoney')->find($userId);
        $couponmoney = $user_coupon['couponmoney'];
        if($couponmoney) { //优惠码 -- 有钱任性
            $leftmoney = $orderItemInfo['price_dis']-$couponmoney;
            $costCouponmoney = ($leftmoney > 0) ? $couponmoney : $orderItemInfo['price_dis'];
            $data = array(
                'record_no'  => $ordersn,
                'ticket_no'  => $ticket_no,
                'user_id'    => $userId,
                'money'      => $costCouponmoney,
                'pay_type'   => 6, //1 网银/2 支付宝/3 微信/4 余额/5 红包/6 优惠券/7 积分
                'salonid'    => $order['salonid'],
                'code_type'  => 2, //1 充值/2 消费/3 退款
                'add_time'   => time(),
            );
            M('fundflow')->add($data);
            M('coupon_statics')->where('id = 1')->setInc('expend_amount',$costCouponmoney); //优惠码使用金额统计+
            M('user')->where("user_id = $userId")->setDec('couponmoney',$costCouponmoney); //优惠码个人总金额-
        }

        if($leftmoney > 0) { //优惠码的钱不够任性
            $newLeftmoney = $leftmoney;
            $user_packet  = M('packet_count')->field('packetmoney')->where("user_id = $userId")->find();
            $packetmoney  = $user_packet['packetmoney'];
            if($packetmoney) { //红包走起
                $newLeftmoney    = $leftmoney - $packetmoney;
                $costPacketmoney = ($newLeftmoney > 0) ? $packetmoney : $leftmoney;
                $data = array(
                    'record_no'  => $ordersn,
                    'ticket_no'  => $ticket_no,
                    'user_id'    => $userId,
                    'money'      => $costPacketmoney,
                    'pay_type'   => 5, //same as up
                    'salonid'    => $order['salonid'],
                    'code_type'  => 2, //same
                    'add_time'   => time(),
                );
                M('fundflow')->add($data);
                M('packet_static')->where('id = 1')->setInc('use_amount',$costPacketmoney); //红包使用金额统计+
                M('packet_count')->where("user_id = $userId")->setDec('packetmoney',$costPacketmoney); //红包个人总金额-
            }
            
            //代金券抵用金额写入流水表
            if($vUseMoney){
                $data = array(
                    'record_no'  => $ordersn,
                    'ticket_no'  => $ticket_no,
                    'user_id'    => $userId,
                    'money'      => $vUseMoney,
                    'pay_type'   => 9, //代金券
                    'salonid'    => $order['salonid'],
                    'code_type'  => 2, //same
                    'add_time'   => time(),
                );
                M('fundflow')->add($data);
            }
            $newLeftmoney = $newLeftmoney - $vUseMoney;
            if($newLeftmoney > 0) { //余额垫后
                $data = array(
                    'record_no'  => $ordersn,
                    'ticket_no'  => $ticket_no,
                    'user_id'    => $userId,
                    'money'      => $newLeftmoney,
                    'pay_type'   => 4, //same as up
                    'salonid'    => $order['salonid'],
                    'code_type'  => 2, //same
                    'add_time'   => time(),
                );
                M('fundflow')->add($data);
            }

            //第三方支付需在此继续添加流水记录
        }

        return 1;
         */
    }



    /**
     * 验证项目信息
     * @param $itemInfo     项目信息
     * @param $num          购买数量
     * @return int
     *
     * @by carosn
     */
    public function valiItemInfo(&$itemInfo,$num,$userid){
        if(!isset($itemInfo['exp_time']) || !isset($itemInfo['total_rep']) || !isset($itemInfo['sold'])){
            return 10001;  //参数有误
        }

        //检测有效期
        if($itemInfo['exp_time']){
            if(time()>$itemInfo['exp_time']){
                return 2901;  //已经停止销售
            }
        }

        if($itemInfo['item_type']==1){
            //检测库存
            if ($itemInfo['total_rep']) {
                $aboutSellNum = $itemInfo['sold'] + $num;
                if ($aboutSellNum > $itemInfo['total_rep']) {
                    return 2902;  //项目已经售罄
                }
            }
        }else if($itemInfo['item_type']==2){
            //限时特价库存判断
            //if ($itemInfo['total_rep']) {
                /*$start = strtotime(date('Y-m-d'));
                $end = $start + 86399;
                $thisBuyNums = $this->query('SELECT count(1) nums FROM `cm_order_item` oi left join cm_order o on oi.ordersn=o.ordersn and o.add_time>=' . $start . ' and o.add_time<=' . $end . ' where oi.itemid=' . $itemInfo['itemid'] . ' and o.ispay=2');
                if ($thisBuyNums[0]['nums'] >= $itemInfo['total_rep']) {
                    return 2902;    //项目已经售罄
                }*/
                /**
                v5.4.1 取消时间限制
                $timeRes = $this->saleTimeExp();
                if($timeRes ==1 ){
                    return 6001;
                }else if($timeRes == 2){
                    return 6002;
                }
                 * 
                 */
                $aboutSellNum = $itemInfo['innage'] + $num;
                if ($aboutSellNum > $itemInfo['repertory']) {
                    return 2908;    //项目已经售罄
                }
            //}
        }
              
        //获取限制信息
        /*
        $itemLimit=M('salon_item_buylimit')->field('limit_time,limit_invite,limit_first')->where('salon_item_id='.$itemInfo['itemid'])->find();
         */
        $itemLimit = D('SalonItemAndLimitView')->getInfoByItemId($itemInfo['itemid']);

        if($itemLimit){
            //当限制购买数量时
            if($itemLimit['limit_time']){
                //统计该用户购买过的该项目的数量 并判断
                $numLimitW['myorder.user_id']=$userid;
                $numLimitW['myorder.ispay']=2;
                $numLimitW['itemid']=$itemInfo['itemid'];
                
                /*
                $itemRecord=D('OrderAndItemView')->field('SUM(order_item.num) as nums')->where($numLimitW)->select();

                $buyNum=0;
                if($itemRecord && $itemRecord[0]['nums']){
                    $buyNum=$itemRecord[0]['nums'];
                }
                 */
                $buyNum = $this->getItemUserBuyNum($userid, $itemInfo['itemid']);
                $aboutBuyNums=$buyNum+$num;
                if($aboutBuyNums>$itemLimit['limit_time']){
                    return 2903;  //购买数量已经超出限制
                }
            }

            //项目邀请限制
            if($itemLimit['limit_invite']){
                /*
                $codeW['user_id']=$userid;
                $codeW['salon_id']=$itemInfo['salonid'];

                $codeIs=M('recommend_code_user')->where($codeW)->find();
                //echo M('recommend_code_user')->_sql();
                if(!$codeIs){
                    return 2904;  //没有激活过当前店铺的邀请码
                }
                 */
                $codeUser = D('RecommendCodeUser')->checkUserRecordExists($userid);
                if(!$codeUser || $codeUser['salon_id'] != $itemInfo['salonid'])
                    return 2904;
                
            }

            //首次下单限制
            if($itemLimit['limit_first']){
                $orderCountW['user_id']=$userid;
                $orderCountW['ispay']=2;
                
                /*
                $itemRecord=$this->getCount($orderCountW);
                 */
                $itemRecord = $this->getOrderCount($userid, 2, 0, "");
                if($itemRecord){
                    return 2905;  //  已经下过单
                }
            }
        }
    }


    /**
     * 订单同步到crm
     * @param $ordersn
     * @return bool
     * @author carson
     */
    private function sendToCrm($ordersn) {
        if(!$ordersn){
            return false;
        }
        //curlPostAjax(C('OTHER_URL')['SENDORDER_URL'],array('orderId'=>$ordersn));
        file_put_contents("logs/".date('Y-m-d').'_tongbuenter.log',$ordersn."\r\n",FILE_APPEND);
        //sock_post(C('OTHER_URL')['SENDORDER_URL'],array('orderId'=>$ordersn));
    }


/************************************* 展示相关 *****************************************************************/


    /**
     * 申请退款订单列表
     * @deprecated since version thrift_150709
     * @param $where
     * @param $field
     * @param $page
     * @param $pageSize
     * @param string $order
     * @return mixed
     */
    public function getRefundOrderList($where,$field,$page,$pageSize,$order=''){
        /*
        $model = D('OrderItemNormsCatView');
        $return = $model->field($field)->where($where)->order($order)->page($page,$pageSize)->select();
        //echo $model->_sql();
        return $return;
         */
    }


    /**
     * 申请退款订单信息
     * @deprecated since version thrift_150709
     * @param $where
     * @param $field
     * @return mixed
     */
    public function getRefund($where,$field){
        /*
        $prefix = C('DB_PREFIX');
        $refund = M('order_refund as refund')
            ->where($where)
            ->field($field)
            ->join($prefix.'salon as salon on refund.salonid=salon.salonid')
            ->join($prefix.'order as cmorder on refund.ordersn=cmorder.ordersn')
            ->join($prefix.'order_item as orderitem on cmorder.ordersn=orderitem.ordersn')
            ->join($prefix.'order_ticket as ticket on ticket.ticketno=refund.ticketno')
            ->find();
        return $refund;
         */
    }


    /**
     * @deprecated since version thrift_150709
     * 线上没用，thrift切换项目不对这个方法做处理
     * 删除订单 ，只是修改其状态status
     */
    public function deleteOrder($where1,$where2,$setfield){
        /*
        $this->startTrans();
        $model = M('order_item');
        $result1 = $this->where($where1)->setField($setfield);
        if(!$result1){
            $this->rollback();
            return false;
        }
        $result2 = $model->where($where2)->setField($setfield);
        if($result1 && $result2){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
         */
        
    }
    
    
    public function getFirstOrderSn($userId,$ticketNo){
       //获取首单的订单号
       /*
       $ordersn1 = M("order")->field('ordersn')->where(array('user_id'=>$userId,"ispay" => 2))->order('orderid')->find();
       $ordersn2 = M('order_ticket')->field('otOrdersn')->where(array('ticketno'=>$ticketNo,'user_id'=>$userId))->find();
       if($ordersn1['ordersn'] == $ordersn2['otOrdersn']){
           return true;
       }else{
           return false;
       }
        */
        $firstOrder = $this->getFirstOrderIspay2($userId);
        $ticket = D('OrderTicket')->getTicketByNo($ticketNo);
        if(!empty($firstOrder) && !empty($ticket) && $ticket['userId']==$userId && $ticket['ordersn']==$firstOrder['ordersn'])
            return true;
        else
           return false;
    }
    
    //看订单是否支付
    /**
     * @deprecated since version thrift150709
     * @param type $salonId
     * @return type
     */
    public function  getOrderIsPay($salonId) {
        /*
        $where = "salonid = %d";
        $condition = array($salonId);
        $fields  = array( "isPay");
        $return = $this->field($fields)->where($where,$condition)->find();
        return $return;
        //echo $this->getLastSql();
         */
    }
    
    //通过订单号拿到订单信息
    public function getOrderByOrdersn($orderSn) {
        /*
        return M('order')->where(array('ordersn' => $orderSn))->find();
         */
        $order = $this->getOrderbySn($orderSn);
        if(empty($order))
            return null;
        $return = array(
            'orderid' => $order['orderId'],
            'ordersn' => $order['ordersn'],
            'salonid' => $order['salonId'],
            'user_id' => $order['userId'],
            'num' => $order['num'],
            'priceall' => $order['priceall'],
            'priceall_ori' => $order['priceallOri'],
            'extra' => $order['extra'],
            'ispay' => $order['ispay'],
            'status' => $order['status'],
            'add_time' => $order['addTime'],
            'shopcartsn' => $order['shopcartsn'],
            'companyId' => $order['companyId'],
            'isCompanyPrice' => $order['isCompanyPrice'],
        );
        return $return;
    }
    
    public function getOrderbySn($orderSn)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getOrderByOrderSn', array($orderSn));
    }
    
    //获取参与过激活码活动的首单
     public function getFirstOrderIspay2($userId){
        $thrift = D('ThriftHelper');
        $orders = $thrift->request('trade-center', 'getFirstOrderIspay2', array($userId));
        if(empty($orders))
            return null;
        return array_shift($orders);
         
        /*
        $oWhere['user_id']=$userId;
        $oWhere['ispay']=2;
        $firstOrder=M('order')->where($oWhere)->order('orderid asc')->find();
        return $firstOrder;
         */
    }
    
    
    /**
     * 获取订单列表
     * @param type $userId
     * @param type $ispay 1=>未支付 2=>已支付
     * @param type $status 2未使用，3使用部分，4使用完成，5作废，6申请退款，7退款完成'
     * @param type $page 以0开始
     * @param type $size
     * @return type
     */
    public function getOrderList($userId, $ispay, $status, $page, $size)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getOrderList', array($userId, $ispay, $status, $page, $size));
    }
    
    public function getOrderCount($userId, $ispay, $status, $shopcartsn)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getOrderCountByUserId', array($ispay, $status, $userId, $shopcartsn));
    }
    
    public function getItemUserBuyNum($userId, $itemId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getItemUserBuyNum', array($userId, $itemId));
    }
    
    public function getCompanyInfoBySn($orderSn)
    {
        $order = $this->getOrderbySn($orderSn);
        if(empty($order))
            return null;
        return array('companyId' => $order['companyId'], 'isCompanyPrice' => $order['isCompanyPrice']);
    }
    
    public function getOrderByShopcartSn($shopcartSn)
    {
        /*
        $order = M('order')->where(array('shopcartsn' => $shopcartSn))->select();
        return $order;
        */
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getOrderByShopCartSn', array($shopcartSn));
    }
    
    private function updateOrderIsPayByOrderSn($orderSn, $ispay)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateOrderIsPayByOrderSn', array($orderSn, $ispay, time()));
    }
    
    //通过订单号，获取店铺id，店铺名，项目id ,项目名
    /**
     * @deprecated since version thrift
     * @param type $orderSn
     * @return type
     */
    public function getOrderAndSalonInfo($orderSn){
        /*
        $orderInfo = M('order_item')->field('salonid,itemid,itemname')->where(array('ordersn' => $orderSn))->find();
        $salonName = M('salon')->where(array('salonid' => $orderInfo['salonid']))->getField('salonname');
        $orderAndSalonInfo = array(
            'salonId' => $orderInfo['salonid'],
            'salonName' => $salonName,
            'itemId' => $orderInfo['itemid'],
            'itemName' => $orderInfo['itemname'],
        );
        return $orderAndSalonInfo;
         */
    }
    
    public function updateOrderActuallyPay($orderSn, $actuallyPay)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateOrderActuallyPay', array($orderSn, $actuallyPay));
    }


    /**
     * 检测订单是否是参与过激活码活动 并且是否可退
     * @param $ordersn
     * @return bool
     */
    public function ticketIsCanRefund($ordersn){
        //获取购物车编号
        $orderInfo=$this->getOrderByOrdersn($ordersn);
        if(!$orderInfo){
            $this->error=2002;
            return false;
        }
        $shopcartSn=$orderInfo['shopcartsn'];
        $userId=$orderInfo['user_id'];

        //获取用户的集团信息(包括集团码)
        $companyInfo=D('User')->getCompanyInfoByUserId($userId);
        $companyCode=$companyInfo?$companyInfo['code']:'';

        
        //如果是微信+代金券支付的且ordersn在cm_redpack_get_record表中则不让退款
        if(!$this->wechatCanRefund($ordersn)){
            return false;
        };
        
        
        if($companyCode && $companyInfo['eventConfId']){
            $eventConfId=$companyInfo['eventConfId'];
        }else{
            //获取用户激活的活动码信息
            $recommendCodeInfo = D('RecommendCodeUser')->checkUserRecordExists($userId);

            if (!$recommendCodeInfo) {
                $this->show_log('(ticketIsCanRefund): order [' . $ordersn . '] recode none');
                return true;
            }
            $diviendInfo=D('Dividend')->getInfoByRecommendCode($recommendCodeInfo['recommend_code']);
            $eventConfId=$diviendInfo['eventConfId'];
        }

        if(!$eventConfId){
            $this->show_log('(ticketIsCanRefund): order [' . $ordersn . '] eventConfId none');
            return true;//todo 特别注意 改为true
        }

        //获取活动信息
        $evcW['conf_id']=$eventConfId;
        $eventInfo=M('EventConf')->field('first_refund')->where($evcW)->find();
        if(!$eventInfo){
            $this->show_log('(ticketIsCanRefund): order [' . $ordersn . '] eventInfo none(2002)');
            $this->error=2002;
            return false;
        }

        //如果设置的不能退款
        if($eventInfo['first_refund']==1){
            //如果该单是首单
            $firstOrder=$this->getFirstOrderIspay2($userId);
            if($firstOrder['ordersn']==$ordersn){
                $this->show_log('(ticketIsCanRefund): order [' . $ordersn . '] first order can not refund');
                $this->error=2309;
                return false;
            }
        }
        
        
        
        return true;
    }
    
    private function wechatCanRefund($ordersn){
        //如果是微信+代金券支付的且ordersn在cm_redpack_get_record表中则不让退款
        $wechatAndVoucher=M('redpack_get_record')->where(['ordersn'=>$ordersn])->count();
        \Think\Log::write("wechat-count".$wechatAndVoucher,  \Think\Log::INFO);
        \Think\Log::write("wechat-count-sql".M()->getLastSql(),  \Think\Log::INFO);
        if($wechatAndVoucher){
            $this->error=2310;
            return false;
        }else{
            return true;
        }
    }
}
