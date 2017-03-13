<?php
/**
 *
 * @author carson
 */
namespace Home\Model;

/**
 * @deprecated since version thrift_150709
 */
class RecommendCodeModel{

    protected $errStatus = false;#默认没有错误
    protected $errMsg;

    public function __construct(){

        $this->DividendObj = D('Dividend');
        $this->RecommendCodeUserObj = D('RecommendCodeUser');
        $this->RecommendCodeUserTmpObj = D('RecommendCodeUserTmp');
    }

    /**
     * 用户激活邀请码
     * @deprecated since version thrift_150709
     * @param $userId
     * @param $recommendCode
     */
    public function toUseInviteCode($userId, $recommendCode){
        //检测激活码状态
        $valiRs=$this->validateRecommendCode($recommendCode);
        if($valiRs){
            return $valiRs;
        }

        //检测分红联盟状态
        $dividendInfo = $this->DividendObj->getInfoByRecommendCode($recommendCode);;
        
        /*
        $dividendSetOpen = M("dividend_set")->field(array("open"))->find();
         */
        $dividendSetOpen = D('DividendSet')->getInfo();
        $dividendSetOpen = $dividendSetOpen["open"] != 1 ? true : false;

        //商家为关闭状态,检查临时数据，并写入临时数据
        if ($dividendInfo['status'] == 1 || $dividendSetOpen ) {
            //检查该用户是否填写过激活码了。
            if ($this->RecommendCodeUserTmpObj->checkUserRecordExists($userId)) {
                return 1042;
            }

            //入库激活码信息
            $this->addRecommendCodeUserTmpData($userId, $recommendCode, $dividendInfo['salon_id']);
        } else {
            //检查该用户是否填写过激活码了。
            if ($this->RecommendCodeUserObj->checkUserRecordExists($userId)) {
                return 1042;
            }
            //入库激活码信息
            $this->addRecommendCodeUser($userId, $recommendCode, $dividendInfo['salon_id']);

            //更新消费推荐数
            $this->DividendObj->setRecommendNumInc($dividendInfo['d_id']);
        }
    }


    /**
     * 验证激活码
     * @deprecated since version thrift_150709
     * @param $recommendCode
     * @return int 新插入得用户邀请码对应得自增ID
     */
    public function validateRecommendCode($recommendCode){
        if (empty($recommendCode)) {
            return 1040;
        }

        if (!preg_match('/^\d{4}$/', $recommendCode)) {
            return 1040;
        }
        //检查是否真实激活码
        $result = $this->DividendObj->checkRecommendCodeExists($recommendCode);
        if (!$result) {
            return 1041;
        }
    }


    /**
     * 正常情况下 用户激活邀请码时的关联记录
     * @deprecated since version thrift_150709
     * @param $userId
     * @param $recommendCode
     * @param $salonId
     */
    protected function addRecommendCodeUser($userId, $recommendCode, $salonId){
        //入库激活码信息
        $this->RecommendCodeUserObj->add(array(
            'user_id' => $userId,
            'salon_id' => $salonId,
            'recommend_code' => $recommendCode,
            'add_time' => time(),
        ));
    }


    /**
     * 分红联盟总开关或者店铺的单个开关被关闭情况下 用户激活邀请码时的关联记录
     * @deprecated since version thrift_150709
     * @param $userId
     * @param $recommendCode
     * @param $salonId
     */
    protected function addRecommendCodeUserTmpData($userId, $recommendCode, $salonId){
        //入库激活码信息
        $this->RecommendCodeUserTmpObj->add(array(
            'user_id' => $userId,
            'salon_id' => $salonId,
            'recommend_code' => $recommendCode,
            'add_time' => time(),
        ));
    }
}
