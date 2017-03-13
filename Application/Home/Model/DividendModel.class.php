<?php
/**
 * 分红联盟
 * @author carson
 */
namespace Home\Model;

class DividendModel extends BaseModel {


    /**
     * @deprecated since version thrift_150709
     * @param type $field
     * @param type $where
     * @return type
     */
    public function dividendSalon($field, $where) {
        /*
        $prefix = C('DB_PREFIX');
        $salon = M('dividend as dividend')
            ->field($field)
            ->join($prefix . 'salon as salon on dividend.salon_id=salon.salonid')
            ->where($where)
            ->find();
        return $salon;
         */
    }


    public function getInfoByRecommendCode($recommendCode)
    {
        $thrift = D('ThriftHelper');
        $dividend = $thrift->request('seller-center', 'getDividendByCode', array($recommendCode));
        if(empty($dividend))
            return null;
        $ret = array(
            'salon_id' => $dividend['salonId'],
            'recommend_code' => $dividend['recommendCode'],
            'recommend_num' => $dividend['recommendNum'],
            'status' => $dividend['status'],
            'activity' => $dividend['activity'],
            'eventConfId' => $dividend['eventConfId'],
        );
        return $ret;
        
        /*
        $condition = array(
            'recommend_code' => $recommendCode,
        );
        return $this->where($condition)->find();
         */
    }

    
    /**
     * 
     * @deprecated since version thrift_150709
     * @param type $dividendId
     * @return type
     */
    public function setRecommendNumInc($dividendId)
    {
        /*
        return $this->where('d_id ='.$dividendId)->setInc('recommend_num', 1);
         */
    }


    /**
     * 看邀请码是否存在
     * @param $recommendCode
     * @return bool
     */
    public function checkRecommendCodeExists($recommendCode)
    {
        $code = $this->getInfoByRecommendCode($recommendCode);
        if(!empty($code))
            return true;
        return false;
        /*
        $condition = array(
            'recommend_code' => $recommendCode,
        );
        $result = $this->field(1)->where($condition)->find();
        if ($result == null) {
            return false;
        }
        return true;
         */
    }


    /****
     * @lufangrui time 2015-04-01
     * 获取分红联盟开关是否打开
     * @return bool
     ****/
    public function getDividendSetOpen(){
        $thrift = D('ThriftHelper');
        $dividendSet = $thrift->request('seller-center', 'getDividendSet', array());
        return $dividendSet['open'] == 1 ? true : false;

        /*
        $status = M("dividend_set")->field(array("open"))->find();
        return $status["open"] == 1 ? true : false;
         */
    }


    /****
     * @lufangrui time 2015-04-01
     * @desc 获取店铺是否开启分红联盟的开关
     * @param $salonid int
     * @return bool
     ****/
    public function getSalonDividendOpen($salonid){
        $thrift = D('ThriftHelper');
        $status = $thrift->request('seller-center', 'getDividendBySalonId', array($salonid));
        /*
        $status = $this->field(array("status"))->where("salon_id=%d",array($salonid))->find();
         */
        return $status["status"] == 1 ? false : true;
    }

}
