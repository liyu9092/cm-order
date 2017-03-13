<?php
/****
 * 抢单信息
 ****/

namespace Home\Model;
use Think\Model;

class BountyPushModel extends BaseModel {
    /****
     * 更新消息推送的抢单状态
     ****/
    public function updateReqStatus($bountySn,$reqStatus,$stylistId) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updateReqStatus', array($bountySn,$reqStatus,$stylistId));
        return $return;
    }
    
    /****
     * 更新消息推送的抢单状态
     ****/
    public function updateReqStatusBySnAndReqS($bountySn,$oldReqStatus,$newReqStatus) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updateReqStatusBySnAndReqS', array($bountySn,$oldReqStatus,$newReqStatus));
        return $return;
    }
        
    /**
     * 根据赏金单号获取推送次数
     */
    public function getBountyPushCount($btSn){
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyPushCount', array($btSn));
        return $return;
    }
    
    /**
     * 将信息插入到bounty_push中
     */
    public function addBountyPush($userId,$bountySn,$status,$reqStatus,$stylistId,$ostype,$addTime) {
        $thrift = D('ThriftHelper');
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddBountyPushParam();
        $param->addTime = $addTime; 
        $param->btSn = $bountySn;
        $param->ostype = $ostype;
        $param->reqStatus = $reqStatus;
        $param->status = $status;
        $param->stylistId = $stylistId;
        $param->userId = $userId;
        /*
        $return = $thrift->request('trade-center', 'addBountyPush', array($userId,$bountySn,$status,$reqStatus,$stylistId,$ostype,$addTime));
         */
        $return = $thrift->request('trade-center', 'addBountyPush', array($param));
        return $return;
    }
    
    public function getStylistPushTask($stylistId, $status, $time, $page, $size)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getStylistPushTask', array($stylistId, $status, $time, $page, $size));
        return $return;
    }
    
    public function getStylistNewPushTaskNum($stylistId, $status, $time)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getStylistNewPushTaskNum', array($stylistId, $status, $time));
        return $return;
    }
    
    /**
   *批量添加BountyPush
   */
    public function addAllBountyPush($allPushData)
    {
        $thrift = D('ThriftHelper');
        $params = array();
        foreach($allPushData as $pushInfo)
        {
            $param = new \cn\choumei\thriftserver\service\stub\gen\AddBountyPushParam();
            $param->addTime = $pushInfo['addTime'];
            $param->btSn = $pushInfo['btSn'];
            $param->ostype = $pushInfo['ostype'];
            $param->reqStatus = $pushInfo['reqStatus'];
            $param->status = $pushInfo['status'];
            $param->stylistId = $pushInfo['stylistId'];
            $param->userId = $pushInfo['userId'];
            $params[] = $param;
        }
        $return = $thrift->request('trade-center', 'addAllBountyPush', array($params));
        return $return;  
    }
    
    /**
     *  通过赏金单号和发型师id获取push信息
     * @param type $stylistId
     * @param type $btSn
     * @return type
     */
    public function getBountyPushInfo ($btSn, $stylistId) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyPushInfo', array($btSn, $stylistId));
        return $return;
    }
}