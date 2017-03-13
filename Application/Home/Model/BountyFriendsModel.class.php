<?php
/****
 * 抢单信息
 ****/

namespace Home\Model;
use Think\Model;

class BountyFriendsModel extends BaseModel {
    
    
    /**
     * 向bounty_friends表写记录数据
    */
    public function addBountyFriends($btSn,$name,$needsStr,$reason,$remark,$addTime) {
        $thrift = D('ThriftHelper');
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddBountyFriendsParam();
        $param->addTime = $addTime;
        $param->btSn = $btSn;
        $param->name = $name;
        $param->needsStr = $needsStr;
        $param->reason = $reason;
        $param->remark = $remark;
        $return = $thrift->request('trade-center', 'addBountyFriends', array($param));
        /*
        $return = $thrift->request('trade-center', 'addBountyFriends', array($btSn,$name,$needsStr,$reason,$remark,$addTime));
         */
        return $return;
    }
}