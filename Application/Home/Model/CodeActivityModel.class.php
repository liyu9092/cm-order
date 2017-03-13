<?php
/**
 * 激活码活动处理类
 *
 * @author: carson
 */
namespace Home\Model;
use Think\Exception;
use Think\Model;


class CodeActivityModel {
    private $fkNums=0;
    private $otherData;

    public function setOtherData($data){
        $this->otherData=$data;
    }


    /**
     * 处理订单
     * @param $ordersn
     * @param string $other
     * @return bool
     */
    public function disposeOrder($ordersn,$shopcartsn=''){

        $orderInfo = D('Order')->getOrderBySn($ordersn);

        if(empty($orderInfo) || $orderInfo['ispay'] != 2){
            $this->show_log('(disposeOrder): order ['.$ordersn.'] none');
            return false;
        }

        //获取用户的集团信息(包括集团码)
        $companyInfo=D('User')->getCompanyInfoByUserId($orderInfo['userId']);

        $companyCode=$companyInfo?$companyInfo['code']:'';
        if($companyCode && $companyInfo['eventConfId']){
            $code=$companyCode;
            $eventConfId=$companyInfo['eventConfId'];

            $this->show_log('(disposeOrder): order ['.$ordersn.'] is company code');
        }else{
        //获取用户激活的活动码信息
            $recommendCodeInfo = D('RecommendCodeUser')->checkUserRecordExists($orderInfo['userId']);
            //print_r($recommendCodeInfo);die();

            if (!$recommendCodeInfo) {
                $this->show_log('(disposeOrder): order [' . $ordersn . '] recode none');
                return false;
            }
            $diviendInfo=D('Dividend')->getInfoByRecommendCode($recommendCodeInfo['recommend_code']);
            //print_r($diviendInfo);die();
            $code=$recommendCodeInfo['recommend_code'];
            $eventConfId=$diviendInfo['eventConfId'];
        }

        //判断是否处理
        if($this->isDispose($code,$ordersn,$shopcartsn)){
            $this->show_log('(disposeOrder): order ['.$ordersn.'] is dispose');
            return false;
        }

        //微信红包 【活动码】
        if($code=='3333'){
            if (!$this->disposeWeChatRedPacket($orderInfo, $shopcartsn)) {
                return false;
            }
            $addRs=$this->addDisposeRecord($code,$orderInfo['ordersn'],$shopcartsn,2,0,0,0);
            if(!$addRs){
                $this->show_log('(disposeOrder): order ['.$ordersn.'] addDisposeRecord failed');
            }
        }else{
            if(!$eventConfId){
                $this->show_log('(disposeOrder): order [' . $ordersn . '] eventConfId none');
                return false;
            }
            $eventInfo=$this->checkEvent($eventConfId,$orderInfo,$shopcartsn);
            if(!$eventInfo){
                //$this->show_log('(disposeOrder): order [' . $ordersn . '] checkEvent failed');
                return false;
            }
            $sendRs=$this->sendGift($code,$orderInfo,$eventInfo);
            if(!$sendRs){
                $this->show_log('(disposeOrder): order ['.$ordersn.'] sendGift failed');
                return false;
            }
        }

        $this->show_log('(disposeOrder): order ['.$ordersn.'] addDisposeRecord success');
    }


    /**
     * 发放礼品处理
     * @param $orderInfo    订单信息
     * @param $eventInfo    活动信息
     * @return bool
     */
    public function sendGift($code,$orderInfo,$eventInfo){
        $ordersn=$orderInfo['ordersn'];

        $userInfo = D('User')->getUserById($orderInfo['userId']);
        if(!$userInfo['mobilephone']){
            $this->show_log('(sendGift): order ['.$ordersn.'] none mobilephone');
            return false;
        }
        $mobilephone=$userInfo['mobilephone'];

        //发送普通营销短信
        if($eventInfo['sms_tmpl_nom']){
            sendphonemsg($mobilephone,$eventInfo['sms_tmpl_nom']);//发送短信
        }

        $dpoType=3;//默认 未发物品  20150917新增的类型
        $confId=$eventInfo['conf_id'];
        $giftCouponId=$eventInfo['giftcoupon_id'];
        $movieTicketId=0;

        //发送礼品资源
        if($eventInfo['sms_tmpl_ticket'] && $eventInfo['giftcoupon_id']){
            $MovieTicketObj=D('Movieticket');

            $MovieTicketObj->startTrans();

            //获取可用券
            $movieTicketInfo=$MovieTicketObj->useATicket($userInfo['user_id'],$mobilephone,$eventInfo['giftcoupon_id'],$eventInfo['conf_id']);
            if(!$movieTicketInfo){
                $this->show_log('(sendGift): order ['.$ordersn.'] '.$MovieTicketObj->getError());
                $MovieTicketObj->rollback();
                return false;
            }

            $dpoType=1;
            $confId=$eventInfo['conf_id'];
            $giftCouponId=$eventInfo['giftcoupon_id'];
            $movieTicketId=$movieTicketInfo['id'];

            /*//记录处理信息
            $disRs=$this->addDisposeRecord($code,$ordersn,$orderInfo['shopcartsn'],1,$confId,$giftCouponId,$movieTicketId);
            if(!$disRs){
                $this->show_log('(sendGift): order ['.$ordersn.'] addDisposeRecord failed');
                $MovieTicketObj->rollback();
                return false;
            }*/
        }

        //记录处理信息
        $disRs=$this->addDisposeRecord($code,$ordersn,$orderInfo['shopcartsn'],$dpoType,$confId,$giftCouponId,$movieTicketId);
        if(!$disRs){
            $this->show_log('(sendGift): order ['.$ordersn.'] addDisposeRecord failed');

            if($dpoType==1){
                $MovieTicketObj->rollback();
            }
            return false;
        }
        if($dpoType==1){

            $MovieTicketObj->commit();

            //发送短信出去
            $MovieTicketObj->sendTicketMsgToMobile($mobilephone,$eventInfo['sms_tmpl_ticket'],$movieTicketInfo);

            //活动里面 发放礼品数增加
            $eventConfNum=D('EventConf')->where('conf_id='.$eventInfo['conf_id'])->setInc('send_gift_num');
            if(!$eventConfNum){
                $this->show_log('(sendGift): order ['.$ordersn.'] eventConfNum set failed');
                return false;
            }

            //资源库 使用数、剩余数修改
            $giftNumData['useNum']=array('exp','useNum+1');
            $giftNumData['remainNum']=array('exp','remainNum-1');
            $giftNum=D('GiftcouponConf')->where('id='.$eventInfo['giftcoupon_id'])->save($giftNumData);
            if(!$giftNum){
                $this->show_log('(sendGift): order ['.$ordersn.'] giftNum set failed');
                return false;
            }
        }
        return true;
    }




    /**
     * 判定活动里面的设置条件
     * @param $eventConfId
     * @param $orderInfo
     * @param $shopcartsn
     * @return bool|mixed
     */
    private function checkEvent($eventConfId,$orderInfo,$shopcartsn){
        $ordersn=$orderInfo['ordersn'];

        $evcW['conf_id']=$eventConfId;
        $eventInfo=M('EventConf')->where($evcW)->find();
        if(!$eventInfo){
            $this->show_log('(checkEvent): order [' . $ordersn . '] eventInfo none');
            return false;
        }
        if($eventInfo['status']!=0){
            $this->show_log('(checkEvent): order [' . $ordersn . '] event closed');
            return false;
        }

        if($eventInfo['need_money']>0) {
            //判断是否首单
            if(!$this->isFirstOrder($orderInfo)){
                $this->show_log('(checkEvent) order: ['.$ordersn.'] not first order');
                return false;
            }

            //实付金额必须大于30大洋
            $actuallyPay=$orderInfo['actuallyPay'];
            if($shopcartsn){
                $actuallyPay = 0;
                $orders=D('Order')->getOrderByShopcartSn($shopcartsn);
                foreach ($orders as $o){
                    $actuallyPay += $o['actuallyPay'];
                }
            }
            if($actuallyPay<$eventInfo['need_money']){
                $this->show_log('(checkEvent) order: ['.$ordersn.']  money not enough');
                return false;
            }
        }
        return $eventInfo;
    }


    /**判断是否为首单
     * @param $orderInfo
     * @return bool
     */
    private function isFirstOrder(&$orderInfo){
        $firstOrder=D('Order')->getFirstOrderIspay2($orderInfo['userId']);
        if(empty($firstOrder))
           return true;
        if(!empty($orderInfo['ordersn']) && $firstOrder['ordersn'] == $orderInfo['ordersn'])
            return true;
        if(!empty($orderInfo['shopcartsn']) && $firstOrder['shopcartsn'] == $orderInfo['shopcartsn'])
            return true;
        return false;
    }


    /**
     * 检测订单是否已经处理过
     * @param $dpoCode
     * @param $orderSn
     * @param string $shopcartSn
     * @return bool
     */
    public function isDispose($dpoCode,$orderSn,$shopcartSn=''){

        $where['dpoCode']=$dpoCode;
        $where['dpoOrderSn']=$orderSn;
        if($shopcartSn){
            $where['dpoShopcartSn']=$shopcartSn;
        }
        $rs=M('dispose_order')->where($where)->find();
        if($rs){
           return true;
        }
        return false;

        /*$thrift = D('ThriftHelper');
        $disposeOrder = $thrift->request('trade-center', 'getDisposeOrder', array($orderSn));
        if(empty($disposeOrder)) {
            return false;
        }else if($disposeOrder['dpoCode'] == $dpoCode && $disposeOrder['dpoShopcartSn'] == $shopcartSn) {
            return ture;
        }
        
        return false;*/
        
    }


    /**
     * 处理记录
     * @param $dpoCode          码号
     * @param $orderSn          订单号
     * @param $shopcartSn       购物车号
     * @param $dpoType          发放物品类型:1自配活动物品 2微信红包
     * @param $eventConfId      活动配置id
     * @param $giftCouponId     资源库id
     * @param $movieticketId    资源id
     * @return mixed
     */
    private function addDisposeRecord($dpoCode,$orderSn,$shopcartSn,$dpoType,$eventConfId,$giftCouponId,$movieticketId){

        $data['dpoCode']=$dpoCode;
        $data['dpoOrderSn']=$orderSn;
        $data['dpoShopcartSn']=$shopcartSn;
        $data['dpoType']=$dpoType;
        $data['eventConfId']=$eventConfId;
        $data['giftCouponId']=$giftCouponId;
        $data['movieticketId']=$movieticketId;

        $data['dpoAddTime']=time();

        return M('dispose_order')->add($data);
    }


    /**
     * 微信送红包活动
     * @param $orderInfo
     * @param string $shopcartsn    订单号 非必须
     * @return bool
     */
    private function disposeWeChatRedPacket(&$orderInfo,$shopcartsn=''){
        $this->show_log('(WeChatRedPacket) is in');
        
        if(!$this->isFirstOrder($orderInfo)){
            $this->show_log('(WeChatRedPacket) order: ['.$orderInfo['ordersn'].'] not first order');
            return false;
        }
        
        //实付金额必须大于30大洋
        $actuallyPay=$orderInfo['actuallyPay'];
        if($shopcartsn){
            $shopWhere['shopcartsn']=$shopcartsn;
            /*
            $actuallyPay=M('order')->where($shopWhere)->sum('actuallyPay');
             */
            $actuallyPay = 0;
            $orders=D('Order')->getOrderByShopcartSn($shopcartsn);
            foreach ($orders as $o){
                $actuallyPay += $o['actuallyPay'];
            }
        }
        if($actuallyPay<30){    //todo 红包金额上线改成  30
            $this->show_log('(WeChatRedPacket) order: ['.$orderInfo['ordersn'].']  money not enough');
            return false;
        }

        //是否微信支付
        /*
        $pWhere['ordersn']=$orderInfo['ordersn'];
        $pWhere['payid']=2;
        $rs=M('payment_log')->field('tn')->where($pWhere)->find();
         */
        $paymentlog = D('PaymentLog')->getPaymentLogByOrderSn($orderInfo['ordersn']);
        if(empty($paymentlog) || $paymentlog['payid'] != 2)
            $rs = null;
        else
            $rs = array('tn' => $paymentlog['tn']);
        if(!$rs){
            $this->show_log('(WeChatRedPacket) order: ['.$orderInfo['ordersn'].'] order not wechatpay');
            return false;
        }
        if(!$this->otherData['mchId']){
            $this->show_log('(WeChatRedPacket) order: ['.$orderInfo['ordersn'].'] mchId not found');
            return false;
        }
        $ret=$this->sendToWeChatRedPacket($rs['tn'],3000,$orderInfo['ordersn']);//todo 红包金额上线改成  3000

        if($ret){
            return true;
        }
        
        return false;
    }


    /**
     * 发送微信红包
     * @param $tn           微信订单号
     * @param $money        红包金额
     * @param $ordersn      本店订单号
     * @return bool|mixed
     */
    public function sendToWeChatRedPacket($tn,$money,$ordersn){
        $Des=new \Think\NetDesCrypt();
        $Des->setKey('authorlsptime20141225000');

        $url=C('OTHER_URL')['WECHAT_REDPACKET'];
        $data=array(
            'id'=>1,
            'total_amount'=>$money,// 金额单位： 分
            'transaction_id'=>$tn,
            'mch_id'=>$this->otherData['mchId'],
        );
        //print_r($data);
        $dataStr=http_build_query($data);
        $dataStr=$Des->encrypt($dataStr);

        $data=array(
            'code'=>$dataStr
        );

        try{
            $ret=curlGet($url,$data);
        }catch (Exception $e){
            $this->show_log('(WeChatRedPacket) order: ['.$ordersn.'] error:=>'.$e->getMessage());
            return false;
        }
        $this->show_log('(WeChatRedPacket) order: ['.$ordersn.'] ret:'.$ret);
        $ret=json_decode($ret,true);
        $this->show_log('(WeChatRedPacket) order: ['.$ordersn.'] ret:'.var_export($ret,true));
        if(!$ret){
            $this->show_log('(WeChatRedPacket) order: ['.$ordersn.'] ret: ret none');
            return false;
        }
        if($ret['result_code']!='SUCCESS'){
            $this->show_log('(WeChatRedPacket) order: ['.$ordersn.'] ret: send failed');
            return false;
        }
        $this->show_log('(WeChatRedPacket) order: ['.$ordersn.'] ret: send success');
        return $ret;
    }


    /**
     * 电影票活动
     * @param $orderInfo    订单信息
     * @return bool
     */
    private function disposeMovie(&$orderInfo){
        $this->show_log('is in');
        //超过10次就不递归了
        if($this->fkNums>=10){
            $this->show_log('nums biger');
            return false;
        }
        $this->fkNums++;
        //if(time()>1435334400){
        //    $this->show_log('time is over');
        //    return false;
        //}
        //$this->show_log('2');
        $where['user_id']=$orderInfo['userId'];
        $where['ispay']=2;
        if($orderInfo['shopcartsn']){
            $where['shopcartsn']=array('neq',$orderInfo['shopcartsn']);;
        }
        $orderNums=D('Order')->getCount($where);
        if($orderNums>1){
            $this->show_log('user: ['.$orderInfo['userId'].'] not first order');
            return false;
        }
        $userInfo = D('User')->getUserById($orderInfo['userId']);
        if(!$userInfo['mobilephone']){
            $this->show_log('none mobilephone');
            return false;
        }
        //$this->show_log('3');
        $mobilephone=$userInfo['mobilephone'];
        $MovieTicketObj=M('movieticket');

        $chWhere['userId']=$userInfo['user_id'];
        $movieTicketInfo=$MovieTicketObj->where($chWhere)->find();

        if(!$movieTicketInfo){
            //$this->show_log('4');
            $MovieTicketObj->startTrans();

            $movieTicketInfo=$MovieTicketObj->where('userId is null')->find();
            if(!$movieTicketInfo){
                $this->show_log('movieticket is over');
                return false;
            }
            $upData['userId']=$userInfo['user_id'];
            $upData['mobilephone']=$mobilephone;
            $setRs=$MovieTicketObj->where('id='.$movieTicketInfo['id'].' and userId is null')->save($upData);
            //$this->show_log('5');
            if(!$setRs){
                $this->show_log('movieticket set failed');
                $MovieTicketObj->rollback();
                $this->disposeMovie($orderInfo);
                return false;
            }
            $MovieTicketObj->commit();
            //$this->show_log('6');
        }
        $this->show_log('last');
        $smstxt = ' 尊敬的用户您好！【臭美美发】为您送上看购网2D电影兑换券1张，兑换密码为【'.$movieTicketInfo['ticketPwd'].'】 有效期至:2016-6-22 客服电话:0755-82181011 影院查询请点击          www.kangou.cn';

        try{
            sendphonemsg($mobilephone,$smstxt);//发送短信
        } catch(Exception $e){
            $this->show_log('msg is over');
        }

        return true;
    }


    private function show_log($info){
        \Think\Log::write($info,'INFO-LSP');
    }
}