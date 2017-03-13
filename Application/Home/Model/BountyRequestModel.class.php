<?php
/****
 * 抢单信息
 ****/

namespace Home\Model;
use Think\Model;

class BountyRequestModel extends BaseModel {
    /****
     * 通过赏金单号获取抢单信息
     ****/
    public function getBountyRequestList($bountySn,$page,$pageSize) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyRequestList', array($bountySn,$page,$pageSize));
        return $return;
    }
    
      /****
     * 根据造型师id和赏金单号更新抢单状态 1未抢到 2抢到
     ****/
    public function updateBountyReqBrStatus($hairstylistId,$btSn,$brStatus) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updateBountyReqBrStatus', array($hairstylistId,$btSn,$brStatus));
        return $return;
    }
    /**
     * 添加BountyRequest信息到表中
     */
    public function addBountyRequest($btSn,$salonId,$stylistId,$brStatus,$addTime, $remark='') {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'addBountyRequest', array($btSn,$salonId,$stylistId,$brStatus,$addTime,$remark));
        return $return;
    }
    /**
     * 根据btSn获取造型师抢单数
     */
    public function getBountyRequestCountBySn($bountySn)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyRequestCountBySn', array($bountySn));
        return $return;
    }
    
    
    public function getBountyRequest($btSn, $stylistId) {
         $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyRequest', array($btSn, $stylistId));
        return $return;
    }
    
}