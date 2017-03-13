<?php
/*
 * 激活邀请码的用户 下单时分红联盟处理类
 *
 * @author carson
 */
namespace Home\Model;


class RecommendCodeOrderModel extends BaseModel{


    /**
     * 下单时邀请码记录关联
     * @param $orderSn
     * @return bool
     * @throws Exception
     */
    // type =1 表示订单的， 2表示赏金单的
    public function toRecordItOnOrder($orderSn,$type =1){
        if(empty($orderSn)) {
           $this->show_log('订单号有误');
        }
        if($type == 1){
            /*
            $where['ordersn']=$orderSn;
            //$where['ordersn']=$orderSn;
            $order = M('order')->where($where)->find();
             */
            $order = D('order')->getOrderBySn($orderSn);
            if (empty($order)) {
                $this->show_log('订单号有误');
            }
            //如果用户激活过邀请码，则需要记录推荐订单信息
            /*
            $recommendUserInfo = D('RecommendCodeUser')->checkUserRecordExists($order['user_id']);
             */
            $recommendUserInfo = D('RecommendCodeUser')->checkUserRecordExists($order['userId']);
        }
        //赏金单
        if($type == 2){
            /*
            $where['btSn']=$orderSn;
            //$where['ordersn']=$orderSn;
            $order = M('bounty_task')->where($where)->find();
             */
            $order = D('Bounty')->getBountyTaskBybtSn($orderSn);
            if (empty($order)) {
                $this->show_log('赏金单号有误');
            }
            //如果用户激活过邀请码，则需要记录推荐订单信息
            $recommendUserInfo = D('RecommendCodeUser')->checkUserRecordExists($order['userId']);
        }
       
        //print_r($recommendUserInfo);
        if(!$recommendUserInfo){
            $this->show_log('recommend none');
            return false;
        }

        // 检测分红联盟总设置开关是否开启
        $DividendObj = D('Dividend');
        $devidendSet = $DividendObj->getDividendSetOpen();
        if(!$devidendSet){
            $this->show_log('dividend close');
            return false;
        }

        // 检测店铺是否开启分红联盟的开关
        $salonDividend = $DividendObj->getSalonDividendOpen($recommendUserInfo["salon_id"] );
        if(!$salonDividend){
            $this->show_log('shop close');
            return false;
        }

        $this->addOrder(array(
                'recommend_code' => $recommendUserInfo['recommend_code'],
                'ordersn' => $orderSn,
                'type' => $type,
            ));
        $this->show_log('is ok');
    }


    /**
     * 写入记录
     * @param $data
     * @return mixed
     */
    private function addOrder($data){
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'addRecommendCodeOrder', array($data['recommend_code'], $data['ordersn'], time(), time()));
        /*
        $time = time();
        $data['add_time'] = $time;
        $data['update_time'] = $time;

        return $this->add($data);
         */
    }
    
    public function getRecommendCodeOrder($ordersn)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getRecommendCodeOrder', array($ordersn));
    }
}