<?php

namespace Home\Controller;

class OrderController extends OrderOutController{

    
    /**
     * 确认订单接口
     * By zhigui.zhang@choumei.cn
     * Date 2015-04-21
     */
    public function beforeSubmitOrder() {

        $paramData = $this->param;
        $salonId   = intval($paramData['salonId']);
        $itemId    = intval($paramData['itemId']);
        $salonNormsId = intval($paramData['salonNormsId']);
        if(!$salonId || !$itemId ) $this->error(1);

        // 初始化返回数据
        $this->ret = array(
            "main"=>array(),
        );

        /*
        $item = M('salon_item')->field('itemid as itemId,itemname as itemName')->where("status = 1 and salonid = {$salonId} and itemId = {$itemId}")->find(); //项目
        if(!$item){
            $this->error(2003);
        }
        
        $salon = M('salon')->field('salonname')->where("salonid = {$salonId}")->find();  //得到店铺名称
        if(!$salon) $this->error(2007);
        */
        $itemInfo=D('SalonItem')->getItem($itemId,$salonId);
        if(!$itemInfo)
            $this->error(2003);
        $item = array(
            'itemId' => $itemInfo['itemid'],
            'itemName' => $itemInfo['itemname'],
        );
        $salonName = D('Salon')->getSalonNameById($salonId);
        if(empty($salonName)) $this->error(2007);

        $priceInfo=D('Order')->getOrderPrice($this->userId,$itemId,$salonNormsId);
        if(!$priceInfo){
            $this->error(2001);
        }

        //整合项目信息
        $item['hasRule'] = $priceInfo['hasrule'];
        $item['totalMoney']  = floatval($priceInfo['price_dis']);
        $item['salonName'] = $salonName;
        $item['normsStr']=$priceInfo['normsStr'];

        $user_id = $this->userId;
        /*
        if($user_id) $mobilephone = M('user')->where("user_id = $user_id")->getField('mobilephone');
         */
        if($user_id) $user = D('User')->getUserById($user_id);
        $mobilephone = $user['mobilephone'];
       
        $SalonItemAndLimit=D('SalonItemAndLimitView');
        list($item['saleRule'],$item['useLimit'])=$SalonItemAndLimit->getItemlimitInfoMark($itemId);

        $mobilephone && $item['mobilePhone'] = $mobilephone;
        $item['isBindPhone'] = isset($mobilephone)? 1 : 0;
        $this->ret["main"] = $item;
        $this->success();

    }

    /**
     * 提交订单接口
     * By zhigui.zhang@choumei.cn
     * Date 2015-04-22
     */
    public function submitOrder(){
        if(empty($this->userId)){
            $this->error(1);
        }

        $sourcetype = getSourcetype();
        !$sourcetype && $sourcetype=1; //测试开启
        if(!in_array($sourcetype,array(1,2,3))) $this->error(1);

        $paramData = $this->param;
        $salonId = intval($paramData['salonId']);
        $itemId    = intval($paramData['itemId']);
        $salonNormsId = intval($paramData['salonNormsId']);
        $num = 1; //订单提交默认参数为1
        if(!$itemId || !$salonId) $this->error(1);

        // 初始化返回数据
        $this->ret = array(
            "main"=>array(),
        );

        $OrderObj=D('Order');
        $adRs = $OrderObj->addOrder($this->userId, $itemId, $salonNormsId);
        if(!$adRs){
            $this->error($OrderObj->getError());
        }

         $main = array(
             'orderSn'  => $adRs
         );
         $OrderObj->commit();
         $this->ret["main"] = $main;
         $this->success();
    }

    /**
     * 支付界面接口
     * By zhigui.zhang@choumei.cn
     * Date 2015-04-22
     */
    // 5.4接口增加选择代金券
    public function confirmOrder(){
        if(empty($this->userId)){
            $this->error(1);
        }
        $userId = $this->userId;
        $paramData = $this->param;
        $orderSn    = $paramData['orderSn'];
        if(!$orderSn) $this->error(1);
        //默认选择代金券最高的券
        // vCancelStatus = 0 表示取消使用代金券  1表示使用代金券
        $voucherObj = D('Voucher');
        $allUniqueVoucherIds = $voucherObj->getVoucherIds($userId,$orderSn,1);
        //print_r($allUniqueVoucherIds);exit;
        if(empty($allUniqueVoucherIds[0])){
            $main['haveCanUsedVoucher'] = 0;
        }else{
            $main['haveCanUsedVoucher'] = 1;
        }
       
        $OrderObj=D('Order');
        /*
        $orderInfo = $OrderObj->getOrderAndOrderItemBySnAndUserId($orderSn,$this->userId);
        if(!$orderInfo) $this->error(2002);

        $order      = $orderInfo['order'];
        $orderItem = $orderInfo['order_item'];
        $salon      = $orderInfo['salon'];
        if(!$order || !$orderItem || !$salon) $this->error(2002);
        
        $itemInfo = M('salon_item')->where('itemid='.$orderItem['itemid'].' and status = 1')->find(); //项目
        if(!$itemInfo){
            $this->error(2003);
        }
        $itemCkRs=$OrderObj->valiItemInfo($itemInfo,$order['num'],$order['user_id']);
        */
        $order = $OrderObj->getOrderbySn($orderSn);
        if(empty($order) || $order['userId'] != $this->userId) $this->error(2002);
        $orderItemInfo = D('OrderItem')->getOrderItemByOrderId($order['orderId']);
        if(empty($orderItemInfo)) $this->error(2002);
        $orderItem = array(
            'orderid'  => $orderItemInfo['orderId'],
            'ordersn'  => $orderItemInfo['ordersn'],
            'itemid'   => $orderItemInfo['itemId'],
            'user_id'  => $orderItemInfo['userId'],
            'salonid'  => $orderItemInfo['salonId'],
            'itemname' => $orderItemInfo['itemname'],
            'num'      => $orderItemInfo['num'],
            'price_dis' => $orderItemInfo['priceDis'],
            'priceall'  => $orderItemInfo['priceall'],
            'priceall_ori' => $orderItemInfo['priceall_ori'],
            'extra'        => $orderItemInfo['extra'],
            'normsStr'     => $orderItemInfo['normsStr'],
            'end_time'     => $orderItemInfo['endTime'],
            'service_detail' => $orderItemInfo['serviceDetail'],
            'useLimit' => $orderItemInfo['useLimit'],
            'salonNormsId' => $orderItemInfo['salonNormsId']
        );
        $salon = D('Salon')->getSalonById($orderItem['salonid']);
        if(empty($salon)) $this->error(2002);
        $item=D('SalonItem')->getItemById($orderItem['itemid']);
        if(!$item)
            $this->error(2003);
        
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

        $itemCkRs=$OrderObj->valiItemInfo($itemInfo,$order['num'],$order['userId']);
        if($itemCkRs){
            $this->error($itemCkRs);
        }
        
        /*
        $user = M('user')->field('money,mobilephone,costpwd')->find($this->userId);
         */
        $user=D('user')->getUserById($this->userId);
        $user['issetpwd'] = $user['costpwd'] ? 1 : 0;
        unset($user['costpwd']);

        list($item['saleRule'],$item['useLimit'])=D('SalonItemAndLimitView')->getItemlimitInfoMark($orderItem['itemid']);

        //获取项目最新价格
        $OrderObj->startTrans();
        $newPrice=$OrderObj->updateOrderOnPriceChanged($order,$orderItem);
        if(!$newPrice){
            $OrderObj->rollback();
            $this->error($OrderObj->getError());
        }
        $OrderObj->commit();
//        print_r($main);exit;
        $main['itemId'] = $orderItem['itemid'];
        $main['itemName'] = $orderItem['itemname'];
        $main['hasRule'] = $orderItem['normsStr'] ? 1 : 0;
        $main['normsStr'] = $orderItem['normsStr'];
        $main['salonName'] = $salon['salonname'];
        $main['saleRule'] = $item['saleRule'];
        $main['useLimit'] = $item['useLimit'];
        $main['totalMoney'] = intval($newPrice);
        $main['totalNums'] = $order['num'];
        $main['balance'] = $user['money'];
        $main['isenough'] = (($user['money']-$newPrice)>=0) ? 1 : 0;
        $main['paymoney'] = (($user['money']-$newPrice)>=0) ? 0 : intval($newPrice-$user['money']);
        /*
        $main = array(
            'itemId'    => $orderItem['itemid'],
            'itemName' => $orderItem['itemname'],
            'hasRule' => $orderItem['normsStr'] ? 1 : 0,
            'normsStr' => $orderItem['normsStr'],
            'salonName'   => $salon['salonname'],
            'saleRule' => $item['saleRule'],
            'useLimit' => $item['useLimit'],
            'totalMoney' => floatval($newPrice),
            'totalNums' => $order['num'],
            'balance' => $user['money'],
            'isenough'  => (($user['money']-$newPrice)>=0) ? 1 : 0,
            'paymoney'  => (($user['money']-$newPrice)>=0) ? 0 : floatval($newPrice-$user['money']),            
        );
         */
        
        
        if($paramData['vCancelStatus'] == 1){
            //传入代金券vId
            if($paramData['vId']){
                $vId = intval($paramData['vId']);
                $voucherObj = D('Voucher');
                $vidCanBeUsedRes = $voucherObj->isVidCanBeUsed($vId,$userId,$orderSn);
                //代金券不可用，给出提示
                if(!$vidCanBeUsedRes){
                    //$this->error(7005);
                    $main['msg'] = '代金券不可用,请重新选择';
                }else{
                    //代金券可以使用,获取抵用金额
                    $useMoneyByVoucher = $voucherObj->getUseMoneyByVoucherId($orderSn,$vId);  
                    //获取代金券活动名称
                    /*
                    $vcTitle = M('voucher')->where(array('vId' => $vId))->getField('vcTitle');
                     */
                    $voucher = $voucherObj->getVoucherById($vId);
                    $vcTitle = $voucher['vcTitle'];
                    $main['useMoneyByVoucher'] = $useMoneyByVoucher;
                    $main['vId'] = $vId;
                    $main['vcTitle'] = $vcTitle;
                    $main['orderSn'] = $orderSn; 
                    $main['totalMoneyToVoucher'] = intval($newPrice-$useMoneyByVoucher);
                    $main['isenough'] = (($user['money']+$useMoneyByVoucher-$newPrice)>=0) ? 1 : 0;
                    $main['paymoney'] = (($user['money']+$useMoneyByVoucher-$newPrice)>=0) ? 0 : intval($newPrice-$user['money']-$useMoneyByVoucher);
                }
                //判断此订单是否有绑定过代金券
                /*
                $res1 =  M('voucher')->where(array('vOrderSn' => $orderSn))->count();
                if($res1){
                    //如果有绑定过，将其解绑,把订单号更新为空
                    M('voucher')->where(array('vOrderSn' => $orderSn ,'vId' => array('neq',$vId)))->save(array('vOrderSn' => ''));
                }
                 */
                $orderVoucher = $voucherObj->getVocherByOrdersn($orderSn);
                if(!empty($orderVoucher))
                    $voucherObj->unbindOrder($orderVoucher['vId']);
            }else{
                //不传入代金券，但是有可以使用的代金券，找出默认的代金券
                if(!empty($allUniqueVoucherIds[0])){
                    $vId = $allUniqueVoucherIds[0];
                    //代金券可以使用,获取抵用金额
                    $useMoneyByVoucher = $voucherObj->getUseMoneyByVoucherId($orderSn,$vId);
                    //获取代金券活动名称
                    /*
                    $vcTitle = M('voucher')->where(array('vId' => $vId))->getField('vcTitle');
                     */
                    $voucher = $voucherObj->getVoucherById($vId);
                    $vcTitle = $voucher['vcTitle'];
                    $main['useMoneyByVoucher'] = $useMoneyByVoucher;
                    $main['vId'] = $vId;
                    $main['vcTitle'] = $vcTitle;
                    $main['orderSn'] = $orderSn; 
                    $main['totalMoneyToVoucher'] = intval($newPrice-$useMoneyByVoucher);
                    $main['isenough'] = (($user['money']+$useMoneyByVoucher-$newPrice)>=0) ? 1 : 0;
                    $main['paymoney'] = (($user['money']+$useMoneyByVoucher-$newPrice)>=0) ? 0 : intval($newPrice-$user['money']-$useMoneyByVoucher);
                     //判断此订单是否有绑定过代金券
                    /*
                    $res2 =  M('voucher')->where(array('vOrderSn' => $orderSn))->count();
                    if($res2){
                        //如果有绑定过，将其解绑,把订单号更新为空
                        M('voucher')->where(array('vOrderSn' => $orderSn ,'vId' => array('neq',$vId)))->save(array('vOrderSn' => ''));
                    }
                     */
                    $orderVoucher = $voucherObj->getVocherByOrdersn($orderSn);
                    if(!empty($orderVoucher))
                        $voucherObj->unbindOrder($orderVoucher['vId']);
                }

            }       
        }else{
            /*
            //判断此订单是否有绑定过代金券
            $res =  M('voucher')->where(array('vOrderSn' => $orderSn))->count();
            if($res){
                //如果有绑定过，将其解绑,把订单号更新为空
                M('voucher')->where(array('vOrderSn' => $orderSn))->save(array('vOrderSn' => ''));
                //此时实付金额就是订单金额
                //获取订单的价格
                $priceall = M('order')->where(array('ordersn' => $orderSn))->getField('priceall');
                //修改订单的实付金额
                M('order')->where(array('ordersn' => $orderSn))->save(array('actuallyPay' => $priceall));
                
            }
            */
            $orderVoucher = $voucherObj->getVocherByOrdersn($orderSn);
            if(!empty($orderVoucher))
            {
                $voucherObj->unbindOrder($orderVoucher['vId']);
                $OrderObj->updateOrderActuallyPay($orderSn, $newPrice);
            }
        }
        
        $this->ret["main"] = $main;
        $this->success();
    }


    /**
     * 余额支付接口
     * By zhigui.zhang@choumei.cn
     * Date 2015-04-22
     */
    public function confirmPay(){
        if(empty($this->userId)){
            $this->error(1);
        }

        $paramData = $this->param;
        //获取请求用户登录信息
        $userDeviceInfo = $this->from;  
        $orderSn   = $paramData['orderSn'];
        if(!$orderSn || !is_numeric($orderSn)) $this->error(1);

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
        //订单已支付，不需重复处理
        if($orderInfo['ispay'] == 2)
            $this->success();
        
        if(empty($orderInfo)) $this->error(2002);
        $order = array(
            'orderid' => $orderInfo['orderId'],
            'ordersn' => $orderInfo['ordersn'],
            'salonid' => $orderInfo['salonId'],
            'priceall' => $orderInfo['priceall'],
            'user_id' => $orderInfo['userId'],
        );
        $orderItemInfo = D('OrderItem')->getOrderItemByOrderId($orderInfo['orderId']);
        if(empty($orderItemInfo)) $this->error(2002);
        $orderItem = array(
            'order_item_id' => $orderItemInfo['orderItemId'],
            'ordersn' => $orderItemInfo['ordersn'],
            'salonid' => $orderItemInfo['salonId'],
            'itemid' => $orderItemInfo['itemId'],
            'num' => $orderItemInfo['num'],
            'price_dis' => $orderItemInfo['priceDis'],
            'end_time' => $orderItemInfo['endTime'],
            'salonNormsId' => $orderItemInfo['salonNormsId'],
            'user_id' => $orderItemInfo['userId'],
            );
	    
        //传入代金券vId
        if($paramData['vId']){
            $vId = intval($paramData['vId']);
            //查找代金券是否绑定过
            $voucherObj = D('Voucher');
            $bindRes = $voucherObj->isVidBind($vId);
            if($bindRes != $orderSn){
                $this->error(7007);
            }
             //代金券可以使用,获取抵用金额
            $useMoneyByVoucher = $voucherObj->getUseMoneyByVoucherId($orderSn,$vId);          
            $OrderObj=D('Order');
            $OrderObj->startTrans();
            $res = $OrderObj->todoMoneyPay($userDeviceInfo,$user_id, $order, $orderItem,$useMoneyByVoucher);
            if(!$res){
                $OrderObj->rollback();
                $this->error($OrderObj->getError());
            }
            else
            {
                $OrderObj->commit();
                $this->success();
            }
        }else{
            $OrderObj=D('Order');
            $OrderObj->startTrans();
            $res = $OrderObj->todoMoneyPay($userDeviceInfo,$user_id, $order, $orderItem);
            if(!$res){
                $OrderObj->rollback();
                $this->error($OrderObj->getError());
            }
            else
            {
                $OrderObj->commit();
                $this->success();
            }
        }       
        
        
    }



/***********************************其他相关*************************************************/

    //未付款订单
    /**
     * @deprecated since version the_very_beginning
     * 业务从来就没有上线，thrift切换不对这个做处理
     */
    public function notPayOrder() {
        //$paramData = $this->getpostparam();
        if(empty($this->userId)){
            $this->error(1);
        }
        $paramData = $this->param;
        $pageSize   = $paramData['pageSize'] ? $paramData['pageSize'] : 20;
        $page  =  $paramData['page'] ? $paramData['page'] : 1;
        //$ispay  = $paramData['status'] ? $paramData['status'] : 1;

        $where = array('cmOrder.ispay' => 1,'cmOrder.status'=>2, 'cmOrder.user_id' => $this->userId,'cmOrder.shopcartsn'=>'');
        $field = array('salonId','salonName','itemId','itemName','norms','priceAll','priceOri','img','total_rep','sold','itemType','orderId','orderSn','salonItemStatus');
        $order = 'addTime desc';
        $model = D('Order');
        $list = $model->getRefundOrderList($where,$field,$page,$pageSize,$order);
        foreach($list as &$v){
            $v['isCanPay']=1;//是否可以支付 1是 2否

            $v['repertory']=0;
            if($v['total_rep']){
                if($v['total_rep']>$v['sold']){
                    $v['repertory']=$v['total_rep']-$v['sold'];
                }
                if(!$v['repertory']){
                    $v['isCanPay']=2;
                }
            }
            if($v['salonItemStatus'] != 1){
                $v['isCanPay']=2;
            }
            $v['priceAll'] = floor($v['priceAll']);
            $v['priceOri'] = floor($v['priceOri']);

            unset($v['total_rep']);
            unset($v['sold']);
        }
        //计算条数
        $where2 = array('ispay'=>1,'status'=>2,'user_id'=> $this->userId,'shopcartsn'=>'');
        $count = $model->getCount($where2);
        if( empty($totalNum) )
                $this->ret["other"]["totalNum"] = $count;
        else
                $this->ret["other"]["totalNum"] = $totalNum;

        if( empty( $this->ret["other"]["totalNum"] ) )
                $this->success( $this->ret );
        $this->ret["main"] = $list;
        $this->success();
    }


    //未付款订单详情
    /**
     * @deprecated since version the_very_beginning
     * 业务从来就没有上线，thrift切换不对这个做处理
     */
    public function notPayOrderDetail(){
        /*
        $id = intval($this->param['orderId']);
        if(!$id) $this->error(1);//参数不太对哦

        $field = array(
            'salonId',
            'salonName',
            'itemId',
            'itemName',
            'norms',
            'priceAll',
            'priceOri',
            'img',
            'orderSn',
            'status',
            'addTime',
            'useLimit',
            'itemType',
            'total_rep',
            'sold',
            'salonItemStatus'
            );
        $where = "cmOrder.orderId = $id";
        $model = D('OrderItemNormsCatView');
        $orderInfo = $model->field($field)->where($where)->find();
        if(!$orderInfo){
            $this->error(1);
        }

        $orderInfo['addTime'] = date('Y-m-d H:i:s',$orderInfo['addTime']);
        list($orderInfo['saleRule'],$useLimit) = D('SalonItemAndLimitView')->getItemlimitInfoMark($orderInfo['itemId']);

        //处理是否支付、库存问题
        $orderInfo['isCanPay']=1;//是否可以支付 1是 2否
        $orderInfo['repertory']=0;
        if($orderInfo['total_rep']){
            if($orderInfo['total_rep']>$orderInfo['sold']){
                $orderInfo['repertory']=$orderInfo['total_rep']-$orderInfo['sold'];
            }
            if(!$orderInfo['repertory']){
                $orderInfo['isCanPay']=2;
            }
        }
        //项目下架或删除，也不能支付
        if($orderInfo['salonItemStatus'] != 1){
            $orderInfo['isCanPay']=2;
        }
        unset($orderInfo['total_rep']);
        unset($orderInfo['sold']);
        
        //转化成整数
        $orderInfo['priceAll'] = floor($orderInfo['priceAll']);
        $orderInfo['priceOri'] = floor($orderInfo['priceOri']);
        /**
         * 数组中值为null的改为空""
         */
        /*
        foreach($orderInfo as &$v){
            if (is_null($v)) {
                $v = '';
            }
        }
        $this->ret['main'] = $orderInfo;
        $this->success();
         */
    }


    /**
     * @deprecated since version thrift_150709
     * 线上没用，thrift切换项目不对这个方法做处理
     * 删除订单  后台数据不删除，只是修改订单状态status
     */
    public function deleteOrder(){
        $paramData = $this->param;
        if(!$paramData['orderId'])
            $this->error(1);//参数不太对哦
        else
            $orderId = $paramData['orderId'];
        if(empty($this->userId)){
            $this->error(1);
        }
        $model = D('order');
        $where1 = array('orderid'=> $orderId,'ispay'=>1,'status'=>2);
        $where2 = 'orderid = '.$orderId;
        $setfield = array('status'=>20);
        $res = $model->deleteOrder($where1,$where2,$setfield);
        if($res){
            $this->success();
        }else{
            $this->error(2201);
        }
    }
    
    /**
     * 初始化评价数据
     * By zhigui.zhang@choumei.cn
     * Date 2015-04-22
     */
    public function commentInit()
    {
        $paramData = $this->param;
        $data['itemid'] = $paramData['itemId'];
        $data['salonid'] = $paramData['salonId'];
        if(!$paramData['itemId'] || !$paramData['salonId']) $this->error(1);

        $result = D('SalonItemComment')->commentInit($data);
        if(is_array($result))
        {
            $this->success(array('main'=>$result));
        }
        else
        {
            $this->error($result);
        }

    }
    
    //评价操作
    public function doComment(){
        //$paramData = $this->getpostparam();
        $paramData = $this->param;
        $data['order_ticket_id'] = $paramData['orderTicketId'];
        $data['salonid'] = $paramData['salonId'];
        $data['itemid'] = $paramData['itemId'];
        $data['content'] = $paramData['content'];
        $data['imgsrc'] = $paramData['imgSrc'];
        $data['add_time'] = time();
        $data['user_id'] = $this->userId;
        $data['satisfyType'] = $paramData['satisfyType'];
        $data['satisfyRemark'] = $paramData['satisfyRemark']?$paramData['satisfyRemark']:0;
        if(!$paramData['orderTicketId'] || !$paramData['salonId'] || !$paramData['itemId'] || !$paramData['satisfyType']) $this->error(1);

        //增加判断
        if(!in_array($data['satisfyType'],array(1,2,3))){
            $this->error(1);
        }
        $satisfy = array(1=>'satisfyOne',2=>'satisfyTwo',3=>'satisfyThree');
        $satisfy = $satisfy[$data['satisfyType']];
        if(is_numeric($paramData['type']) && $paramData['type'] == 2){  //修改评论
            $result = D('SalonItemComment')->changeComment($data, $satisfy);
        }
        else {
            //评价时增加造型师评价,该好评的时候只需要更新
            $data['hairstylistid'] = empty($paramData['hairstylistid'])?0:$paramData['hairstylistid'];
            //每个订单只能评价一次
            /*
            $count = M('salon_itemcomment')->where('order_ticket_id = %d',$paramData['orderTicketId'])->count();
            if($count){
                $this->error(5001);
            }
             */
            $comment = D('SalonItemComment')->getCommentByTicketId($paramData['orderTicketId']);
            if($comment){
                $this->error(5001);
            }
            $result = D('SalonItemComment')->doComment($data, $satisfy);
        }
        if(is_array($result))
            $this->success(array('main'=>$result));
        else 
            $this->error($result);
    }

    
    //评论详情
    public function commentDetail(){
        if(empty($this->userId)){
            $this->error(1);
        }

        $paramData = $this->param;
        $order_ticket_id = $paramData['orderTicketId'];
        if(!$this->userId || !$order_ticket_id){
            $this->error(1);
        }
        // 初始化返回数据
        $this->ret = array(
                "main"=>array()
        );
        //$where = 'comment.order_ticket_id='.$order_ticket_id;
        $where = array('comment.order_ticket_id'=>$order_ticket_id,'comment.user_id'=>$this->userId);
        $data = D('SalonItemComment')->comment($where);
        if(empty($data)){
            $this->success( $this->ret );
        }
        //print_r($data);exit;
        $data['img'] = ($data['img']) ? json_decode($data['img']):array();  //照片为空时，需要返回为一个数组
        $data['satisfyRemark'] = D('SalonItemComment')->getRemark($data['satisfyRemark']);
        $data['addTime'] = date('Y-m-d',$data['addTime']);
        $this->ret["main"] = $data;
        $this->success();

    }

    //评论列表 
    /*
    public function commentList(){
        //$paramData = $this->getpostparam();
       // print_r($paramData);exit;
        $params = $this->param;
        $page = empty( $params["page"] ) ? 1 : $params["page"];
        $pageSize = empty($params["pageSize"]) ? 10 : $params["pageSize"] ;
        $totalNum = $params["totalNum"];
        $userId = $this->userId;
        //echo $userId;exit;
        if(empty($userId)){
            $this->error( 10001 );
        }
        // 初始化返回数据		
        $this->ret = array(
                "main"=>array(),
                "other"=>array(
                        "totalNum"=>0
                )
        );
        //通过用户id + 票的状态status （4表示使用完成）获取订单项目id + 卷号 + 是否评论 + 使用时间
        $field = array('order_item_id','ticketno','use_time','iscomment');
        $where = array("user_id"=>$userId,"status"=>4);
        $res = M('OrderTicket')->field($field)->where($where)->page($page,$pageSize)->select();
        //echo count($res);exit;
        //print_r($res);exit;
        if( empty($totalNum) )
                $this->ret["other"]["totalNum"] = count($res);
        else
                $this->ret["other"]["totalNum"] = $totalNum;

        if( empty( $this->ret["other"]["totalNum"] ) )
                $this->success( $this->ret );
        //print_r($res);
        $orderItemModel = D("OrderItem");
        $salonModel = D("Salon");
        $formatsModel = D( "SalonItemFormats" );
        $formatModel = D( "SalonItemFormat" );
        foreach ($res as $key => $val) {
            $this->ret["main"][$key]['isComment'] = $val['iscomment'];
            $this->ret["main"][$key]['ticketNo'] = $val['ticketno'];
            $this->ret["main"][$key]['useTime'] = $val['use_time'];
            //$this->ret["main"][$key]['itemId'] = $val['order_item_id']; 
            $orderInfo = $orderItemModel->getOrderInfoById( $val["order_item_id"] );
            //通过item_id找到满意度
            $this->ret["main"][$key]['satisfyType'] = '';
            if($val['iscomment'] == 2){
                $satisfyType = M('salon_itemcomment')->field('satisfyType')->where('itemid='.$orderInfo['itemid'])->find();
                $this->ret["main"][$key]['satisfyType'] = $satisfyType['satisfyType'];
            }
            $extra = $orderInfo['extra'];
            if( !empty( $extra ) ) {
                $tmpExtra = explode( "," , $extra );
                $listExtra = '';
                foreach( $tmpExtra as $k => $v ){
                        $tempNorms = $formatModel->getItemForamtName( $v );
                        //print_r($tempNorms);
                        $childName = $tempNorms["format_name"];
                        $parentFormatId = $tempNorms["salon_item_formats_id"];
                        $parentName = $formatsModel->getItemForamtName( $parentFormatId );
                        $listExtra = $parentName . " : " .$childName;
                        if($k < count($tmpExtra)-1){
                            $listExtra = $listExtra."、";
                        }
                }
                $this->ret["main"][$key]['listExtra']  = empty($listExtra)? "无规格":$listExtra;
            }else{
                $this->ret["main"][$key]['listExtra'] = "无规格";
            }
            $salonInfo = $salonModel->getSalonNameById($orderInfo['salonid']);
            $this->ret["main"][$key]["salonName"] = $salonInfo["salonname"];
            $this->ret["main"][$key]['itemId'] = $orderInfo['itemid'];
            $this->ret["main"][$key]['itemName'] = $orderInfo['itemname'];
            $this->ret["main"][$key]['priceAll'] = $orderInfo['priceall'];
            $this->ret["main"][$key]['salonId'] = $orderInfo['salonid'];
           
        }
        $this->success();       
        
        
    }
     */
    
     /**
     * 评价列表
     * @by:  huliang
     */
    /*
    public function commentList() {

        $user_id   = $this->userId;
        if(empty($user_id)){
            $this->error(1);
        }
        // 初始化返回数据		
        $this->ret = array(
                "main"=>array(),
                "other"=>array(
                        "totalNum"=>0
                )
        );
        $paramData = $this->param;
        $page = intval($paramData['page']);
        $page = $page ? $page : 1;
        //print_r($paramData);exit;
        $num = intval($paramData['pageSize']);
        $num = $num ? $num : 10;
        $where  = array('order_ticket.user_id'=>$user_id,'status'=>4);
        $ticket = D('SalonItemTicketView')->where($where)->page($page,$num)->order("iscomment,use_time desc")->select();
        //记录总条数
        $where2 = array('user_id'=>$user_id,'status'=>4);
        $count = M('order_ticket')->where($where2)->count();
        if( empty($totalNum) )
                $this->ret["other"]["totalNum"] = $count;
        else
                $this->ret["other"]["totalNum"] = $totalNum;

        if( empty( $this->ret["other"]["totalNum"] ) )
                $this->success( $this->ret );
        //if(!$ticket) $this->exitjson('',52);
        //if($page >1 && !$ticket) $this->exitjson('',51);
       // print_r($ticket);exit;
        if($ticket) {
            foreach($ticket as $key => $val) {
               // $ticket[$key]['use_time'] = date('Y-m-d H:i:s',$val['use_time']);
                $this->ret["main"][$key]['order_ticket_id'] = $val['order_ticket_id'];
                $this->ret["main"][$key]['useTime'] = date('Y-m-d H:i:s',$val['use_time']);
                $this->ret["main"][$key]['isComment'] = $val['iscomment'];
                $this->ret["main"][$key]['ticketNo'] = $val['ticketno'];
                $this->ret["main"][$key]['priceAll'] = $val['price_dis'];
                $this->ret["main"][$key]["salonName"] = $val["salonname"];
                $this->ret["main"][$key]['itemId'] = $val['itemid'];
                $this->ret["main"][$key]['itemName'] = $val['itemname'];
                $this->ret["main"][$key]['priceAll'] = $val['price_dis'];
                $this->ret["main"][$key]['salonId'] = $val['salonid'];
                //通过item_id找到满意度
                $this->ret["main"][$key]['satisfyType'] = '';
                if($val['iscomment'] == 2){
                    $satisfyType = M('salon_itemcomment')->field('satisfyType')->where('itemid='.$val['itemid'].' and order_ticket_id='.$val['order_ticket_id'])->find();
                    $this->ret["main"][$key]['satisfyType'] = $satisfyType['satisfyType'];
                }

                $this->ret["main"][$key]['listExtra'] = D('SalonItemFormatsView')->getFormatStr($val['extra']);;
            }
            //$ticket = D('Packet')->judge($ticket,C('OTHER_URL')['WEIXIN_PACKETURL']); //判断红包的情况
            //$this->show_log(var_export($ticket,true));
        }

        //求评价数量
        /*$where  = array('status'=>4,'user_id'=>$user_id,'iscomment'=>2);
        $pwhere = array('status'=>4,'user_id'=>$user_id,'iscomment'=>1);
        $count    = M('order_ticket')->where($where)->count();
        $notcount = M('order_ticket')->where($pwhere)->count();*/
    /*
        //$return = array('result'=>1,'count'=>$count,'notcount'=>$notcount);
        //$this->ret["main"] = $ticket;
        $this->success();
    }
    */
    
    public function commentList()
    {
        if(empty($this->userId)){
            $this->error(1);
        }
        // 初始化返回数据		
        $this->ret = array(
            "main"=>array(),
            "other"=>array(
                "totalNum"=>0
            )
        );
        $paramData = $this->param;
        
        $count = D('OrderTicket')->getTicketNumByUserId($this->userId, 4);
        if( empty($paramData['totalNum']) )
            $this->ret["other"]["totalNum"] = $count ?: 0;
        else
            $this->ret["other"]["totalNum"] = $paramData['totalNum'] ?: 0;

        if( empty( $this->ret["other"]["totalNum"] ) )
                $this->success( $this->ret );
        $page = intval($paramData['page']);
        $page = $page ? $page : 1;
        $num = intval($paramData['pageSize']);
        $num = $num ? $num : 10;
        $ticket = D('OrderTicket')->getTicketByUserId($this->userId, 4, $page-1, $num, 2);
        if(!$ticket)
            $this->success();
        foreach($ticket as $key => $val) {
            $orderItem = D('OrderItem')->getItemById($val['orderItemId']);
            if(empty($orderItem))
                continue;

            $this->ret["main"][$key]["salonName"] = D('Salon')->getSalonNameById($orderItem['salonId']);
            $this->ret["main"][$key]['order_ticket_id'] = $val['orderTicketId'];
            $this->ret["main"][$key]['useTime'] = date('Y-m-d H:i:s',$val['useTime']);
            $this->ret["main"][$key]['isComment'] = $val['iscomment'];
            $this->ret["main"][$key]['ticketNo'] = $val['ticketno'];
            $this->ret["main"][$key]['itemId'] = $orderItem['itemId'];
            $this->ret["main"][$key]['itemName'] = $orderItem['itemname'];
            $this->ret["main"][$key]['priceAll'] = $orderItem['priceDis'];
            $this->ret["main"][$key]['salonId'] = $orderItem['salonId'];
            //通过item_id找到满意度
            $this->ret["main"][$key]['satisfyType'] = '';
            if($val['iscomment'] == 2){
                $satisfyType = D('SalonItemComment')->getCommentByTicketId($val['orderTicketId']);
                $this->ret["main"][$key]['satisfyType'] = $satisfyType['satisfyType'];
            }

            $this->ret["main"][$key]['listExtra'] = D('SalonItemFormatsView')->getFormatStr($val['extra']);;
        }
        $this->success();
    }
    
    /**
     * 获取卷号
     */
    public function getTicket()
    {
        if (empty($this->userId))
            $this->error(1);
        
        // 初始化返回数据		
        $this->ret = array(
            "main" => array()
        );
        $paramData = $this->param;
        $ordersn = $paramData['orderSn'];
        if (!$ordersn || !is_numeric($ordersn))
            $this->error(1);

        $user=D('User')->getUserById($this->userId);
        $mobilephone = $user['mobilephone'];
        if (!$mobilephone)
            $this->error(1002);

        $order = D('Order')->getOrderbySn($ordersn);
        if($order['ispay'] != 2 || $order['userId'] != $this->userId)
            $this->error(2301);
            

        /*
        $ticket = D('OrderItemTicketView')->where("order_item.ordersn = {$ordersn}")->find();
         */
        $orderItem = D('OrderItem')->getOrderItemByOrderId($order['orderId']);
        if(empty($orderItem)) $this->error(2301);
        $ticketData = D('OrderTicket')->getTicketByOrderItemId($orderItem['orderItemId']);
        if(empty($ticketData)) $this->error(2301);
        $ticket['ordersn'] = $orderItem['ordersn'];
        $ticket['salonid'] = $orderItem['salonId'];
        $ticket['itemname'] = $orderItem['itemname'];
        $ticket['price_dis'] = $orderItem['priceDis'];
        $ticket['priceall'] = $orderItem['priceall'];
        $ticket['extra'] = $orderItem['extra'];
        $ticket['itemid'] = $orderItem['itemId'];
        $ticket['useLimit'] = $orderItem['useLimit'];
        $ticket['user_id'] = $ticketData['userId'];
        $ticket['ticketno'] = $ticketData['ticketno'];
        $ticket['status'] = $ticketData['status'];
        $ticket['order_ticket_id'] = $ticketData['orderTicketId'];
        $ticket['isSendPhoneMsg'] = $ticketData['isSendPhoneMsg'];
        
        $ticketInfo=array();
        //print_r($ticket);exit;
        $itemname = $ticket['itemname'];
        $ticketno = $ticket['ticketno'];
        //沙龙.项目   
        $salonname = D('Salon')->getSalonNameById($ticket['salonid']);

        $isPay = $order['ispay'];

        $ticketInfo['goodname'] = $salonname . '.' . $ticket['itemname'];
        $ticketInfo['isPay'] = $isPay;
        $priceall = $ticket['priceall'];
        $ticketInfo['ticketNo'] = $ticket['ticketno'];
        $ticketInfo['itemName'] = $ticket['itemname'];
        $ticketInfo['salonName'] = $salonname;
        //根据项目id获取商品类型和限制描述
        
        $ticketInfo['userLimit'] = $ticket['useLimit'] ?: "";
        //规格
        $ticketInfo['normsStr'] = D('SalonItemFormatsView')->getFormatStr($ticket['extra']);

        $point = floor($priceall);
        //如果有代金券，则成长值要去掉代金券抵用金额
        $orderVoucher = D('Voucher')->getVocherByOrdersn($ordersn);
        $isExistVid = $orderVoucher['vId'];
        if($isExistVid){
            $UseMoneyByVoucher = D('Voucher')->getUseMoneyByVoucherId($ordersn,$isExistVid);
            $point = $point - $UseMoneyByVoucher;
        }  
        $ticketInfo['growth'] = $point;
        //获取增值服务
        $item = D("SalonItem")->getItemInfo($ticket['itemid']);
        $ticketInfo['useLimit'] = $item['useLimit'];
        $ticketInfo['addedService'] = array();
        if (!empty($item['addserviceStr'])) 
        {
            //先判断当前的增值服务和分类的增值服务是否符合
            $addserviceStr = rtrim(trim($item['addserviceStr'], ","), ',');
            $addserviceArr = explode(",", $addserviceStr);
            $addedServiceItemType = D('AddServiceItemType')->getItemTypeAddService($item['typeid']);
            if (!empty($addedServiceItemType) && !empty($addedServiceItemType['serviceDetail'])) 
            {
                $serviceDetailStr = trim($addedServiceItemType['serviceDetail'], ",");
                $serviceDetailArr = explode(",", $serviceDetailStr);
                $diff = array_diff($addserviceArr, $serviceDetailArr);
                if(count($diff)>0)
                    $addService = D('AddService')->getAddServicesById($serviceDetailArr);
                else 
                    $addService = D('AddService')->getAddServicesById($addserviceArr);
            }
            $ticketInfo['addedService'] = $addService;
        }
        $this->ret = array(
            'main' => array(
                $ticketInfo
            )
        );
        D('User')->updateUserGrowth($this->userId, $point);
        $tRs=D('OrderTicket')->getTicketNoByUserId($this->userId);
        $otherStr='';
        if(!$tRs || $tRs['ticketno']==$ticketno){
            $otherStr='[首单]';
        }
        //判断是否发送过短信，避免从新发送,IsSendPhoneMsg = 0 表示未发送
        if(!$ticket['isSendPhoneMsg']){
            //目前只发一条，后面需要拼接
            $smstxt = "您的臭美券密码：{$ticketno}({$itemname})，{$salonname}，消费时请出示此密码。臭美承诺：不办卡、不推销、全正品。".$otherStr;
            D('Sms')->sendSmsByType($mobilephone, $smstxt,2);
            D('OrderTicket')->updateTicketSendMsgStatus($ticketno);
        }
        $this->success();
    }
}
