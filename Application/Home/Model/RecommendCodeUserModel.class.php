<?php
/**
 * 邀请码激活关联
 * @author carson
 */
namespace Home\Model;

class RecommendCodeUserModel extends BaseModel {

    /**
     * 检测用户是否已经填写过激活码了
     * @param $userId
     * @return bool
     */
    public function checkUserRecordExists($userId)
    {
        $thrift = D('ThriftHelper');
        $recommendUser = $thrift->request('seller-center', 'getRecommendUserByUserId', array($userId));
        if(empty($recommendUser))
            return false;
        $ret = array(
            'user_id' => $recommendUser['userId'],
            'salon_id' => $recommendUser['salonId'],
            'recommend_code' => $recommendUser['recommendCode'],
            'id' => $recommendUser['id'],
        );
        return $ret;
        
        /*
        $record = $this->where('user_id = ' . $userId)->find();
        if ($record == null) {
            return false;
        }
        return $record;
         */
    }
    //根据用户id，获取用户邀请码，通过邀请码获取是否是活动的
    public function isActivity($userId){
        $user = $this->checkUserRecordExists($userId);
        if($user == false)
            return false;
        
        $dividend = D('Dividend')->getInfoByRecommendCode($user['recommend_code']);
        if(empty($dividend) || $dividend['activity'] != 1)
            return false;
        return true;
        /*
        $prefix = C('DB_PREFIX');
        $model = M('recommend_code_user as recommend');
        $res = $model->field('dividend.activity')->where(array('user_id' => $userId))->join($prefix.'dividend as dividend on dividend.recommend_code=recommend.recommend_code')->find();
        if($res['activity'] == 1){
            return true;
        }else{
            return false;
        }
         */
    }
    
    //通过ordersn找到RecommendCodeOrder
    public function getRecommendCodeOrder($ordersn){
        $thrift = D('ThriftHelper');
        $order = $thrift->request('trade-center', 'getRecommendCodeOrder', array($ordersn));
        if(empty($order))
            return null;
        $ret = array(
            'id' => $order['id'],
            'ordersn'   => $order['ordersn'],
            'add_time'  => $order['addTime'],
            'update_time'       => $order['updateTime'],
            'recommend_code'    => $order['recommendCode'],
        );
        return $ret;
        /*
        $where['ordersn']=$ordersn;
        $recordInfo=M('recommend_code_order')->where($where)->find();
        return $recordInfo;
         */
    }
}
