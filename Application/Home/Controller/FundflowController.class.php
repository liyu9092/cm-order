<?php

/****
* @author lufangrui
* @desc 臭美卷 相关
* @time 2015-04-14
****/

namespace Home\Controller;

class FundflowController extends OrderOutController {
    
    private $refundDesc = array("去过了，不太满意", "朋友/网上评价不好", "买多了/买错了", "计划有变，没时间去", "后悔了，不想要了", "其他"); //退款描述

    /****
    *	臭美卷列表 ( 未消费/退款卷 )
    ****/
    public function ticketFlow(){
        $time = time();
        // $params = $_POST;
        $params = $this->param;
        $userId = $this->userId;
        // 臭美券状态 notuse：未使用,refund：退款
        $status = $params["status"];

        if( !in_array( $status , array("notuse","refund") ) || empty( $userId ) )
            $this->error(4);

        // 2. 获取信息

        // a. 获取臭美票信息
        $ticketModel = D("OrderTicket");

        if($status == "notuse"){
            $newTotal = $ticketModel->getNonConsumeCount($userId);
            list($start,$pageSize)=$this->getLimitParam($newTotal);

            $list = $ticketModel->getNonConsumeList( $userId , $start , $pageSize );
        }else{
            $newTotal = $ticketModel->getRefundCount($userId);
            list($start,$pageSize)=$this->getLimitParam($newTotal);

            $list = $ticketModel->getRefundList( $userId , $start , $pageSize );
        }
        $datas=array();

        $itemModel = D("SalonItem");
        $salonModel = D("Salon");
        $orderItemModel = D("OrderItem");
        // var_dump( $list );
        // b. 关联项目信息和店铺信息
        foreach( $list as $key => $val ){
            $orderInfo = $orderItemModel->getItemById($val['order_item_id']);
            $itemInfo = $itemModel->getItemInfo( $orderInfo["itemId"] );
            $endTime = $val["end_time"];
            $limitTime = '';
            if(empty($endTime))
            {
                $limitTime = '无限制';
            }else
            {
                if($endTime - time() < 0)
                {
                    $limitTime = '已过期';
                }
                else{
                    $limitTime = date("Y-m-d H:i:s", $endTime);
                }
            }
            $datas[$key]['limitTime'] = $limitTime;
            $datas[$key]["status"] = $val["status"] == 8 ? 6 : $val['status'];
            $datas[$key]["ticketNo"] = $val["ticketno"];
            $datas[$key]["itemid"] = $orderInfo["itemId"];
            $datas[$key]["itemName"] = $orderInfo["itemname"];  //臭美券列表的项目名称一经生成便不能被修改
            $datas[$key]["salonName"] = $salonModel->getSalonNameById( $orderInfo["salonId"] );
            if(empty($val["end_time"])){
                //1表示没过期  2表示已过期
                $datas[$key]["isInvalid"] = 1;
            }else if($time < $val["end_time"]){
                $datas[$key]["isInvalid"] = 1;
            }else{
                $datas[$key]["isInvalid"] = 2;
            }

            if( $itemInfo["itemType"] == 2 )  //item_type为2表示是限时特价
                $datas[$key]["itemType"] = 2;
            else
                $datas[$key]["itemType"] = 1;
        }

        $this->ret['main']=$datas;

        $this->success();
    }

    /****
     * 获取臭美卷详情  ,v5.4版本增加order信息 
     ****/
    public function ticketInfo(){

        // $params = $_POST;
        $params = $this->param;
        //$userId = $params["userId"];
        $userId = $this->userId;
        if(empty($userId)){
            $this->error(1);
        }
        $ticketNo = $params["ticketNo"];
        $addrLati = $params["addrLati"];
        $addrLong = $params["addrLong"];

        if( empty( $ticketNo ) )
            $this->error( 10001 );

        // 初始化返回数据
        $this->ret = array(
            "main" => array(
                "itemInfo"=>array(),
                "ticketInfo"=>array(),
                "salonInfo"=>array(),
                "note"=>""
            )
        );
        //获取是否是首次活动使用
        $activityValue = D('RecommendCodeUser')->isActivity($userId);
        if($activityValue){
            //$ispay = M('order')->where(array('user_id' => $userId ,'ispay' =>2))->order('pay_time')->find();
            $resFirstOrder = D('Order')->getFirstOrderSn($userId,$ticketNo);
            if($resFirstOrder){
                $canRefund = 2;  //不能退
            }else{
                $canRefund = 1;
            }           
        }else{
            $canRefund = 1;
        }
        

        $ticketModel = D( "OrderTicket" );

        // 1. 获取臭美卷信息
        $tempTicketInfo = $ticketModel->getRefundInfo( $ticketNo ,$userId );


        if( empty( $tempTicketInfo ) )
            $this->success( $this->ret );
        // 2. 初始化个信息 方便后面进行组装
        $ticketInfo = array();
        $itemInfo = array();
        $salonInfo = array();
        $note = "";

        // 3. 开始完善每一块的信息

        // a. 查找项目是否有规格 等显示选择的规格详情
        $orderItemModel = D("OrderItem");
        /*
        $formatModel = D( "SalonItemFormat" );
        $formatsModel = D( "SalonItemFormats" );
         */
        $salonItemModel = D("salonItem");
        $salonModel = D("salon");
        $SalonItemFormatsViewObj=D('SalonItemFormatsView');
        $orderModle = D("Order");
        $companyCodeModle = D("CompanyCode");
        // 规格 卷信息组装
        $tempItemInfo = $orderItemModel->getOrderInfoById( $tempTicketInfo["order_item_id"] );

        //通过订单orderid获取到订单中总额,也就是这张券的价格，用于显示在退款券中
        /*
        $orderId = $tempItemInfo['orderid'];
        $where = 'orderid ='.$orderId;
        $orderPriceAll = M('order')->where($where)->getField('priceall');
         */
        
        $order = D('Order')->getOrderbySn($tempItemInfo['ordersn']);
        $orderPriceAll = $order['priceall'];
        
        $ticketInfo["priceAll"] = $orderPriceAll;

        $extra = $tempItemInfo["extra"];
        // 服务详情
        $note = $tempItemInfo["service_detail"];
            // var_dump($tempItemInfo);
        // echo "\n\r";
        // echo $orderItemModel->getLastSql();
        // echo "\n\r";exit;
        $ticketInfo["ticketNo"] = $tempTicketInfo["ticketno"];
        $endTime = $tempTicketInfo["end_time"];
        $limitTime = '';
        if(empty($endTime))
        {
            $limitTime = '无限制';
        }else
        {
            if($endTime - time() < 0)
            {
                $limitTime = '已过期';
            }
            else{
                $limitTime = date("Y-m-d H:i:s", $endTime);
            }
        }
        $ticketInfo["limitTime"] = $limitTime;
        $ticketInfo["status"] = $tempTicketInfo["status"];
                    $ticketInfo["listExtra"] = $SalonItemFormatsViewObj->getFormatStr($extra);
                    //print_r($extra);exit;

        //根据订单id找到集团id，通过集团id找到集团名称
        /*
        $companyInfo = M('order')->field('companyId,isCompanyPrice')->where('orderid ='.$orderId)->find();
        */
        $companyInfo = $orderModle->getCompanyInfoBySn($tempItemInfo['ordersn']);
        
        $companyId = $companyInfo['companyId'];
        $isCompanyPrice = $companyInfo['isCompanyPrice'];
        if($companyId && $isCompanyPrice){
            /*
            $companyAcronym = M('company_code')->where('companyId ='.$companyId)->getField('companyAcronym');           
             */
            $companyAcronym = $companyCodeModle->getCompanyAcronym($companyId);
            $itemInfo['companyAcronym'] = $companyAcronym;
        }else{
            $itemInfo['companyAcronym'] = "臭美";
        }
        // 项目信息组装
        $tempLogoAndType = $salonItemModel->getItemLogoAndType( $tempItemInfo["itemid"] );
        $itemInfo["logo"] = $tempLogoAndType["logo"];
        $itemInfo["itemType"] = $tempLogoAndType["item_type"] == 2 ? 2 : 1;
        
        $itemInfo["itemShowType"] = 1;
        if($tempLogoAndType["item_type"] == 1 && $tempLogoAndType["typeid"] == 8){
            $itemInfo["itemShowType"] = 3;
        }
        if($tempLogoAndType["item_type"] == 2 ){
            $itemInfo["itemShowType"] = 2;
        }
        
        $itemInfo["useLimit"] = $tempLogoAndType["useLimit"];
        $itemInfo["itemName"] = $tempItemInfo["itemname"];
        $itemInfo["price"] = $tempItemInfo["priceall"]; // 总额
        $itemInfo["priceOri"] = $tempItemInfo["priceall_ori"]; // 原总额
        $itemInfo["itemId"] = $tempItemInfo["itemid"];
        // 店铺信息组装
        $salonId = $tempItemInfo["salonid"];
        $tempSalonInfo = $salonModel->getSimplyInfoById( $salonId , $addrLati , $addrLong );

        $salonInfo["salonName"] = $tempSalonInfo["salonname"];
        $salonInfo["logo"] = $tempSalonInfo["logo"];
        //$salonInfo["commentNum"] = $tempSalonInfo["commentnum"];
        /**
         * 显示出具体的点评人数，千分位分割。到达万级时，以万为单位，如：1.3万人点评，四舍五入保留1位小数
         */
        $commentnum = $tempSalonInfo["commentnum"];
        if($commentnum < 1000){
            $salonInfo["commentNum"] = $commentnum;
        }
        else if($tempSalonInfo["commentnum"] > 999 && $tempSalonInfo["commentnum"] < 10000){
            $salonInfo["commentNum"] = number_format($commentnum);
        }else{
            $salonInfo["commentNum"] = round($commentnum/10000,1)."万";
        }

        $salonInfo["goodScale"] = empty($tempSalonInfo["goodScale"]) ? '100%' : round( $tempSalonInfo["goodScale"] , 4 ) * 100 . '%';
        $salonInfo["salonId"] = $tempSalonInfo["salonid"];
        $salonInfo["addr"] = $tempSalonInfo["addr"];
        /**
         * 用户当前地址与商家距离，1KM以内用m为单位，1KM以上，以KM为单位
         */
        $dist = $tempSalonInfo["dist"];
        if(empty($dist)){
            $salonInfo["dist"] = "0m";
        }else if($dist < 1000){
            $salonInfo["dist"] = round($dist)."m";
        }else{
            $salonInfo["dist"] = round($dist/1000,1) . "km";
        }
        //$salonInfo["dist"] = empty($tempSalonInfo["addr"]) ? '0km' : round($tempSalonInfo["dist"]/1000,2) . "km";
        $salonInfo["addrLati"] = $tempSalonInfo["addrlati"];
        $salonInfo["addrLong"] = $tempSalonInfo["addrlong"];
        
        //获取订单相关信息
        $orderSn =  D('OrderTicket')->getOrderSnByTicketNo($ticketNo); 
        if(!$orderSn){
            $this->error(2002);
        }
        //获取订单信息(订单支付时间和订单价格)
        /*
        $orderWhere = array(
            'ordersn' => $orderSn
        );
        $orderRes = M('order')->field('pay_time,priceall')->where($orderWhere)->find();
         */
        $orderRes = array('priceall' => $order['priceall'], 'pay_time' => $order['payTime']);
        //根据订单号获取订单的代金券金额，最后获得抵用金额
        $UseMoneyByVoucher = D('Voucher')->getUseMoneyByVoucher($orderSn,$orderRes['priceall']);
        //组装订单信息
        $orderInfo = array(
            'orderSn' => $orderSn,
            'payTime' => date("Y-m-d H:i",$orderRes['pay_time']),
            'payMoney' => $orderRes['priceall'] - $UseMoneyByVoucher , //包括用户余额支付和第三方支付
            'UseMoneyByVoucher' => $UseMoneyByVoucher ,
        );
        $this->ret["main"]["salonInfo"] = $salonInfo;
        $this->ret["main"]["itemInfo"] = $itemInfo;
        $this->ret["main"]["ticketInfo"] = $ticketInfo;
        $this->ret["main"]["orderInfo"] = $orderInfo;
        $this->ret["main"]["note"] = $note;
        $this->ret["main"]["canRefund"] = $canRefund;
        unset( $tempTicketInfo );
        unset( $tempSalonInfo );
        unset( $tempItemInfo );
        unset( $salonInfo );
        unset( $itemInfo );
        unset( $ticketInfo );
        unset($orderInfo);
        $this->success();
    }


    /**
     * 退款券详情
     */
    public function refundInfo(){
        $paramData = $this->param;
        $ticketNo = $paramData['ticketNo'];
        if(!$ticketNo)
            $this->error(1);

        $this->ret = array(
            "main"=>array()
        );
        
        $refundInfo = D('OrderRefund')->getRefundByTicketNo($ticketNo);
        if(empty($refundInfo)) $this->error(2302);
        $orderInfo = D('Order')->getOrderbySn($refundInfo['ordersn']);
        $ticket = D('OrderTicket')->getTicketByNo($ticketNo);
        $orderItem = D('OrderItem')->getOrderItemByOrderId($orderInfo['orderId']);
        $salon = D('Salon')->getSalonById($orderInfo['salonId']);
        $refund = array(
            'salonId' => $orderInfo['salonId'],
            'salonName' => $salon['salonname'],
            'itemName' => $orderItem['itemname'],
            'itemId' => $orderItem['itemId'],
            'endTime' => $orderItem['endTime'],
            'status' => $ticket['status'],
            'priceAll' => $orderInfo['actuallyPay'],
            'reType' => $refundInfo['retType'],
            'ordersn' => $orderItem['ordersn'],
            'addTime' => $refundInfo['addTime'],
            'optTime' => $refundInfo['optTime'],
        );
        #兼容，后台新启用了状态8(退款中)，旧版前端没办法识别
        $refund['status'] = $refund['status'] == 8 ? 6 : $refund['status']; 
        $status = $refund['status'];
        if($refund){
            //转换时间格式
            if(empty($refund['endTime'])){
                $refund['endTime'] = "无限制";
            }else{
                $refund['endTime'] = date("Y-m-d H:i:s" , $refund['endTime']);
            }
        }
        //初始化退款信息
        $message['one']=array(
            'title' => '提交退款申请',
            'desc'  => date('Y-m-d H:i:s',$refund['addTime']).' 已受理',
            'remark'=> "臭美将在1个工作日内处理您的退款申请。",
        );

        $item = D('SalonItem')->getItemById($refund['itemId']);
        $itemType = $item['itemType'];
        $refund['itemType'] =  empty($itemType)? '':$itemType;

        //1 网银/2 支付宝/3 微信/4 余额/5 红包/6 优惠券/7 积分/8邀请码兑换 /9 代金券/10 易联支付
        $payTypeArr=array(
            2=>array(
                'title'=>'支付宝',
                'tel'=>'95188',
            ),
            3=>array(
                'title'=>'微信',
                'tel'=>'0755-83767777',
            ),
            10=>array(
                'title'=>'易联',
                'tel'=>'020-28863558',
            ),
        );


        $toBalance=0;   //是否退回余额
        $otherType=0;   //是否存在其他退回方式
        $returnType = '余额';//退回方式

        $retype = $refund['reType'];
        if ($retype == 1) { //原路返回
            $fundflows = D('Fundflow')->getFundflowArr($ticketNo);
            $fundflowArr = array();
            foreach($fundflows as $fundflow)
            {
                if($fundflow['pay_type'] != 9 && $fundflow['code_type'] != 3)
                    $fundflowArr[] = $fundflow;
            }
            $fundCount = count($fundflowArr);

            if ($fundCount == 1) { //只有一种支付方式
                $fundflowInfo = $fundflowArr[0];
                if (in_array($fundflowInfo['pay_type'], array(1, 2, 3, 10))) {
                    $payLog = D('PaymentLog')->getPaymentLogByOrderSn($refund['ordersn']);
                    $otherType=$fundflowInfo['pay_type'];   //支付方式
                    $otherMoney=$fundflowInfo['money'];     //支付金额
                    $otherSn=$payLog['tn'];                 //支付流水号
                } else { //余额的原路返回
                    $toBalance = $fundflowInfo['money'];
                }
            } else { //多种组合
                foreach ($fundflowArr as $val) {
                    if (in_array($val['pay_type'], array(1, 2, 3, 10))) {
                        $payLog = D('PaymentLog')->getPaymentLogByOrderSn($refund['ordersn']);
                        $otherType=$val['pay_type'];    //支付方式
                        $otherMoney=$val['money'];      //支付金额
                        $otherSn=$payLog['tn'];         //支付流水号
                    } else {//余额的原路返回
                        $toBalance = $val['money'];
                    }
                }
            }
            if($otherMoney){
                $returnType=$payTypeArr[$otherType]['title'];
                if($otherType==10){
                    $returnType='银行卡';
                }
            }
        } else if ($retype == 2) { //退回臭美余额
            $toBalance = $refund['priceAll'];
        }

        //退款成功时
        if($status==7) {
            $desc = $refund['optTime']?date('Y-m-d H:i:s',$refund['optTime']).' 已完成':'已完成';

            $message['two']=array(
                'title' => '臭美处理完成',
                'desc'  => $desc,
            );

            $message['three']=array(
                'title' => '退款成功',
                'desc'  => $desc,
                'tips'=> '',
            );
            if($toBalance && !$otherType){

                $tips = '已成功退至您的臭美账户，请查看账户余额。若对退款有疑问，请致电臭美客服400-9933-800进行咨询。';

            }else if(!$toBalance && $otherType){

                $tips = '您可以凭借交易号'.$otherSn.'致电'.$payTypeArr[$otherType]['title'].'客服'.$payTypeArr[$otherType]['tel'].'查询到账情况，退款成功到账时间以查询结果为准，通常为3-5天。';

            }else if($toBalance && $otherType){

                $tips = '退款¥'.$toBalance.'已成功退至您的臭美账户，请查看账户余额；';
                $tips .= '退款¥'.$otherMoney.'您可以凭借交易号'.$otherSn.'致电'.$payTypeArr[$otherType]['title'].'客服'.$payTypeArr[$otherType]['tel'].'查询到账情况，退款成功到账时间以查询结果为准，通常为3-5天。';

            }

            $message['three']['tips']=$tips;
        }

        $refund['message'] = $message;
        $refund['route'] = "退回".$returnType;
        $this->ret['main'] = $refund;
        $this->success();
    }

            //退款券详情
    public function refundDetail(){

        $paramData = $this->param;
        if(!$paramData['ticketNo'])
            $this->error(1);//参数不太对哦
        else
            $ticketNo = $paramData['ticketNo'];
            $this->ret = array(
                "main"=>array()
            );
            /*
            $where = 'refund.ticketno='.$ticketNo;
            $field = array(
                'refund.salonid as salonId',
                'salon.salonname as salonName',
                'orderitem.itemname as itemName',
                'orderitem.itemid as itemId',
                'ticket.end_time as endTime',
                'ticket.status',
                'refund.money as priceAll',
                'refund.retype as reType'
            );
            $refund = D('Order')->getRefund($where,$field);
            */
            $refundInfo = D('OrderRefund')->getRefundByTicketNo($ticketNo);
            if(empty($refundInfo))
                $this->error(2310);
            $orderInfo = D('Order')->getOrderbySn($refundInfo['ordersn']);
            $ticket = D('OrderTicket')->getTicketByNo($ticketNo);
            $orderItem = D('OrderItem')->getOrderItemByOrderId($orderInfo['orderId']);
            $salon = D('Salon')->getSalonById($orderInfo['salonId']);
            $refund = array(
                'salonId' => $orderInfo['salonId'],
                'salonName' => $salon['salonname'],
                'itemName' => $orderItem['itemname'],
                'itemId' => $orderItem['itemId'],
                'endTime' => $orderItem['endTime'],
                'status' => $ticket['status'],
                'priceAll' => $orderInfo['actuallyPay'],
                'reType' => $refundInfo['retType'],
            );
            #兼容，后台新启用了状态8(退款中)，旧版前端没办法识别
            $refund['status'] = $refund['status'] == 8 ? 6 : $refund['status']; 
            
            $status = $refund['status'];
            if($refund){
                //转换时间格式
                if(empty($refund['endTime'])){
                    $refund['endTime'] = "无限制";
                }else{
                    $refund['endTime'] = date("Y-m-d H:i:s" , $refund['endTime']);
                }
            }
            
            $item = D('SalonItem')->getItemById($refund['itemId']);
            $itemType = $item['itemType'];
            /*
            $itemModel = D("SalonItem");
            $itemId = $refund['itemId'];
            $itemType = $itemModel->where("itemid=".$itemId)->getField('item_type');
             */
            $refund['itemType'] =  empty($itemType)? '':$itemType;
            //臭美劵退款状态
            $returnType = '余额';
            $retype = $refund['reType'];
            if($retype == 1) { //原路返回
            /*
                $fundflowArr = M('fundflow')->field('pay_type,money')->where(array('ticket_no' => $ticketNo ,"pay_type" => array('neq',9),"code_type" => array('neq',3)))->select();
             */
                $fundflows = D('Fundflow')->getFundflowArr($ticketNo);
                $fundflowArr = array();
                foreach($fundflows as $fundflow)
                {
                    if($fundflow['pay_type'] != 9 && $fundflow['code_type'] != 3)
                        $fundflowArr[] = $fundflow;
                }
                $fundCount   = count($fundflowArr);

                //pay_type 1 网银/2 支付宝/3 微信/4 余额 | 支付宝（微信/银联）
                if($fundCount == 1) { //只有一种支付方式
                    $fundflowArr = $fundflowArr[0];
                    if(in_array($fundflowArr['pay_type'],array(1,2,3))) {
                        if($fundflowArr['pay_type'] == 1) {
                            $returnType = '银联';
                            $returnPath = $returnType;
                        } else if($fundflowArr['pay_type'] == 2) {
                            $returnType = '支付宝';
                            $returnPath = $returnType;
                        } else if($fundflowArr['pay_type'] == 3) {
                            $returnType = '微信';
                            $returnPath = '银行卡';
                        }
                        $message = array(
                            array(
                                'title' => '退款成功',
                                'desc'  => "退款￥{$refund['priceAll']}已返至您的{$returnPath}，请查收。",
                                'tips'  => '注：若您未收到退款，请与臭美客服联系客服电话：0755-88371190',
                                'tag'   => 0
                            ),

                            array(
                                'title' => '臭美审核中',
                                'desc'  => "臭美将在1-2个工作日内，审批您的退款申请。待臭美完成审核后，转由{$returnType}进行下一步操作。",
                                'tag'   => 0
                            )
                        );
                    } else { //余额的原路返回
                        $usermoney = 1;
                    }
                } else { //多种组合
                    foreach($fundflowArr as $val) {
                        if(in_array($val['pay_type'],array(1,2,3))) {
                            if($val['pay_type'] == 1) {
                                $returnType = '银联';
                                $returnPath = $returnType;
                            } else if($val['pay_type'] == 2) {
                                $returnType = '支付宝';
                                $returnPath = $returnType;
                            } else if($val['pay_type'] == 3) {
                                $returnType = '微信';
                                $returnPath = '银行卡';
                            }
                            $otherMoney = $val['money'];
                        } else {
                            $money += $val['money'];
                        }
                    }

                    if(!$otherMoney) { //余额的不同形式
                        $usermoney = 1;
                    } else { //两种
                        $message = array(
                            array(
                                'title' => '退款成功',
                                'desc'  => "退款￥{$money}已退至您的臭美账户；退款￥{$otherMoney}已返至您的{$returnPath}，请查收。",
                                'tips'  => '注：若您未收到退款，请与臭美客服联系客服电话：0755-88371190',
                                'tag'   => 0
                            ),

                            array(
                                'title' => '臭美审核中',
                                'desc'  => "臭美将在1-2个工作日内，审批您的退款申请。待臭美完成审核后，臭美余额部分会立即退款至您的臭美账户，剩余部分转由{$returnType}进行下一步操作。",
                                'tag'   => 0
                            )
                        );
                    }
                }
            } else if($retype == 2){ //退回臭美余额
                $usermoney = 1;
            }

            if($usermoney == 1) { //余额
                $message = array(
                    array(
                        'title' => '退款成功',
                        'desc'  => "退款￥{$refund['priceAll']}已退至您的臭美账户，请查收。",
                        'tips'  => '注：若您未收到退款，请与臭美客服联系客服电话：0755-88371190',
                        'tag'   => 0
                    ),
                    array(
                        'title' => '臭美审核中',
                        'desc'  => '臭美将在1-2个工作日内，审批您的退款申请。待臭美完成审核后，会立即退款至您的臭美账户。',
                        'tag'   => 0
                    )
                );
            }

            if($status == 6) { //申请退款
                $message[1]['tag'] = 1;
                $ticket['message'] = $message;
            } else if($status == 8) { //退款中
                //unset($message[0]);
                //$message = array_values($message);
                $message[1]['tag'] = 1;
                $ticket['message'] = $message;
                //返回前台的状态还是显示申请退款
                $refund['status'] = 6;
            } else { //退款完成
                $message[0]['tag'] = 1;
                $message[1]['tag'] = 1;
                $ticket['message'] = $message;
            }
            //$ticket['route'] = $returnType;
            $refund['message'] = $ticket['message'];
            $refund['route'] = "退回".$returnType;
            $this->ret['main'] = $refund;
            $this->success();
    }


    /**
     * 检测订单是否是参与过激活码活动并且是首单的
     * @param $ordersn
     */
    private function ticketIsCanRefund($ordersn,$userId){
        /*
        $oWhere['user_id']=$userId;
        $oWhere['ispay']=2;
        $firstOrder=M('order')->where($oWhere)->order('orderid asc')->find();
         */
        //todo 记得兼容以前的红包退款
        $firstOrder=D('Order')->getFirstOrderIspay2($userId);
        if(!$firstOrder){
            $this->error(2302);
        }
        $CodeActivityObj=D('CodeActivity');
        $companyCodeA=$CodeActivityObj->companyCodeA;
        $companyCode=D('User')->getUseCompanyCodeByUserId($userId);
        //获取购物车编号
        $orderInfo=D('Order')->getOrderByOrdersn($ordersn);
        $shopcartSn=$orderInfo['shopcartsn'];
        //如果用户是集团用户 并且集团码是在配置里面 那么就是下面处理。 不然就去处理活动码的逻辑
        if($companyCode && in_array($companyCode,$companyCodeA)) {
            //判断是否处理
            if($CodeActivityObj->isDispose($companyCode,$ordersn,$shopcartSn)){
                $this->error(2309);
            }
        }else{
            /*
            $where['ordersn']=$ordersn;
            $recordInfo=M('recommend_code_order')->where($where)->find();
             */
            $recordInfo=D('RecommendCodeOrder')->getRecommendCodeOrder($ordersn);
            if(!$recordInfo){
                return false;
            }
            /*
            $dWhere['recommend_code']=$recordInfo['recommend_code'];
            $dWhere['activity']=1;
            $isRecord=D('Dividend')->where($dWhere)->find();
            //print_r($isRecord);
             */
            $dividend = D('Dividend')->getInfoByRecommendCode($recordInfo['recommendCode']);
            if(empty($dividend) || $dividend['activity'] != 1){
                return false;
            }
            if($firstOrder['ordersn']==$ordersn){
                $this->error(2309);
            }
        }
    }

    
    // 臭美券确认退款
    public function ticketRefundDo() {

        $user_id = $this->userId;
        if(empty($user_id)){
            $this->error(1);
        }
       // print_r($user_id);exit;
        $paramData = $this->param;
        $retype    = $paramData['reType'];
        $rereason  = $paramData['reReason'];
        //$order_ticket_id = intval($paramData['order_ticket_id']);
        $ticketNo = $paramData['ticketNo'];
        if( !$ticketNo || !$retype  ) $this->error(1);
        if( $rereason === '' ) $this->error(2907);

        /*
        $twhere  = array('order_ticket.ticketno'=>$ticketNo,'order_ticket.user_id'=>$user_id,'order_ticket.status'=>2);
        $ticket = D('OrderItemTicketView')->where($twhere)->find();
        if(!$ticket){
            $this->error(2302);
        }
        $this->ticketIsCanRefund($ticket['ordersn'],$user_id);
        
        */
        $ticketInfo = D('OrderTicket')->getTicketByNo($ticketNo);
        if(empty($ticketInfo) || $ticketInfo['userId'] != $user_id)
            $this->error(2302);

        if($ticketInfo['status'] == 4){
            $this->error(2306);
        }else if($ticketInfo['status'] == 6){
            $this->error(2307);
        }else if($ticketInfo['status'] == 7){
            $this->error(2308);
        }else if($ticketInfo['status'] == 8){
            $this->error(2307);
        }
        $orderItem = D('OrderItem')->getItemById($ticketInfo['orderItemId']);
        $OrderObj=D('Order');
        $checkRs=$OrderObj->ticketIsCanRefund($orderItem['ordersn']);
        if(!$checkRs){
            $this->error($OrderObj->getError());
        }
        $ticket['ordersn'] = $orderItem['ordersn'];
        $ticket['salonid'] = $orderItem['salonId'];
        $ticket['itemname'] = $orderItem['itemname'];
        $ticket['price_dis'] = $orderItem['priceDis'];
        $ticket['priceall'] = $orderItem['priceall'];
        $ticket['extra'] = $orderItem['extra'];
        $ticket['itemid'] = $orderItem['itemId'];
        $ticket['useLimit'] = $orderItem['useLimit'];
        $ticket['user_id'] = $ticketInfo['userId'];
        $ticket['ticketno'] = $ticketInfo['ticketno'];
        $ticket['status'] = $ticketInfo['status'];
        $ticket['order_ticket_id'] = $ticketInfo['orderTicketId'];
        //判断订单是否全额为代金券支付，全部为代金券支付的，不能退款
        /*
        $actuallyPay = M('order')->where(array('ordersn' => $ticket['ordersn']))->getField('actuallyPay');
         */
        $order = $OrderObj->getOrderBySn($ticket['ordersn']);
        $actuallyPay = $order['actuallyPay'];
        if($actuallyPay <= 0){
            $this->error(7009);
        }
        /*
        $model = M('order_ticket');
         */
        $model = D('OrderTicket');
        $model->startTrans();
        /*
        $where = array('ticketno'=>$ticketNo,'user_id'=>$user_id);
        $ticketStatus = $model->where($where)->getField('status');
         */
        $ticketStatus = $ticket['status'];
        if($ticketStatus == 2){
            /*
            $res = $model->where($where)->save( array('status'=>6) );
             */
            $res = D('OrderTicket')->updateStatus($ticketNo, 6);
            if(!$res){
                $model->rollback();
                $this->error(2303);
            }
        }else{
            $this->error(2302);
        }

        /*
        $data = array(
                'ordersn'  => $ticket['ordersn'],
                'ticketno' => $ticket['ticketno'],
                'user_id'  => $ticket['user_id'],
                'salonid'  => $ticket['salonid'],
                'money'    => $ticket['price_dis'],
                'retype'   => $retype,
                'rereason' => $rereason,
                'add_time' => time(),
        );
	*
        */
        /*
        $data1['ordersn'] =  $ticket['ordersn'];
        $data1['ticketno'] =  $ticket['ticketno'];
        $data1['user_id'] =  $ticket['user_id'];
        $data1['salonid'] = $ticket['salonid'];
        $data1['money'] =  $ticket['price_dis'];
        $data1['retype'] =  $retype;
        $data1['rereason'] = $rereason;
        $data1['add_time'] = time();
        //除去代金券的钱
        if($actuallyPay > 0){
            $data1['money'] =  $actuallyPay;
        }
        $res2 = M('order_refund')->add($data1);
        */
        if($actuallyPay > 0) {
            $ticket['price_dis'] =  $actuallyPay;
            $res2 = D('OrderRefund')->addOrderRefund($ticket['ordersn'], $ticket['ticketno'], $ticket['user_id'], $ticket['price_dis'], $retype, $ticket['salonid'], $rereason);
            if(!$res2){
                $model->rollback();
                $this->error(2304);
            }
            //劵动态
            /*
            $data = array(
                'ordersn'  => $ticket['ordersn'],
                'ticketno' => $ticket['ticketno'],
                'add_time' => time(),
                'status'   => 6, //状态：2未使用，4使用完成，6申请退款，7退款完成 8 退款拒绝 9 退款失败
                'remark' => $rereason
            );
            $res3 = M('order_ticket_trends')->add($data);
            */
            /*
            $res3 = D('OrderTicketTrends')->addOrderTicketTrends($ticket['ordersn'], $ticket['ticketno'], 6, $rereason);
             */
            $reasons = empty($rereason) ? array() : explode(',', $rereason);
            $reasonstrs = array();
            foreach($reasons as $reason)
            {
                array_key_exists($reason, $this->refundDesc) && $reasonstrs[] = $this->refundDesc[$reason];
            }
            $rereasonstr = implode(";", $reasonstrs);
            $res3 = D('OrderTicketTrends')->addOrderTicketTrends($ticket['ordersn'], $ticket['ticketno'], 6, $rereasonstr);
            if($res3){
                $model->commit();
                $this->success();
            }else{
                $model->rollback();
                $this->error(2305);
            }
        }

    }
}