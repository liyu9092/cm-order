<?php
/**
 * 订单外部接口 公共控制器
 *
 * @author: carson
 */
namespace Home\Controller;

class OrderOutController extends BaseController {
    protected $userId;
    protected $userInfo;
    protected $companyData;
    protected $notNeedCheck = array(
        'Order' => array('beforeSubmitOrder'),
        'Bounty' => array('pushMessage','bountyMessage'),
        'BountyTask' => array('index','ranklist'),
    );

    public function _initialize(){
        parent::_initialize();

        $this->parseParam();
        $this->parseToken();
    }


    /**
     * 处理参数
     */
    protected function parseParam(){
        if(!$_POST['code']){
            $this->error(1);
        }
        $code=$this->parseCode($_POST['code']);
        if(!$code){
            $this->error(3);
        }
        //print_r($code);
        $this->code = $code;
        if(!$code['type'] || !$code['to']){
            $this->error(5);
        }

        if(!isset($code['body'])){
            $this->error(4);
        }
        $this->param = $code['body'];
        if($code['from']){  
            $this->formatFrom();
        }
        if(!defined('SOURCE_TYPE')){
            define('SOURCE_TYPE',getSourcetype($code['from']));
        }
    }
    
    /**
     * 格式化from参数值
     */
    private function formatFrom(){
        $from = array_filter(explode("#",$this->code['from']));
        $formatData= array();
        foreach($from as $key=>$val){
            $newData = explode('=',$val);
            $formatData[$newData[0]] = $newData[1];
        }
        $this->from = $formatData;
    }
    
    


    /**
     * 登录验证
     * @return bool
     */
    private function parseToken(){
        $ctlName=$this->code['type'];
        $actName=$this->code['to'];

        /**
         * userId存在的时候才去取值，这时候不去判断值存不存在或者值的正确性，而是留到最下面去判断。
         * 这里做这种蛋疼的代码逻辑是因为有些接口不需要登录验证，但TM的又有可能会传userId过来取用户信息
         */
        if($this->param['userId']){
            $userIdStr=$this->param['userId'];
            $userId=D('Des')->decrypt($userIdStr);

            if(in_array(strtolower($ctlName), array('stylist'))){
                return $this->setStylist($userId);
            }

            $this->userId=$userId;
            if($userId){
                $userInfo=D('User')->getUserById($userId);
                if(!$userInfo){
                    $this->error(11);
                }
                $this->userInfo=$userInfo;
                $this->companyData=D('User')->checkCompanyRs($userInfo['companyId']);
            }
        }
        //过滤掉不需要验证的
        if(isset($this->notNeedCheck[$ctlName])){
            $funcArr=$this->notNeedCheck[$ctlName];
            if(empty($funcArr)){
                $this->error(1000);
            }
            if(in_array($actName,$funcArr)){
                return true;
            }
        }

        //判断值是否存在 以及值的正确性
        if(!$this->userId){
            $this->error(11);
        }
        if(!$userInfo){
            $this->error(11);
        }
    }


    protected function setStylist($stylistId) {
        if(!$stylistId)
            $this->error(11);
        
        /*
        $userInfo=M('hairstylist')->where("stylistId={$stylistId} and status=1")->find();
         */
        $userInfo = D('HairStylist')->getHairstylist($stylistId);
        if(!$userInfo)
            $this->error(11);

        $this->userId=$stylistId;
        $this->stylistId=$stylistId;
        $this->stylist = $userInfo;
    }


    //电话号码检查
    protected function phonecheck($mobilephone) {

        if (empty($mobilephone) || !is_numeric($mobilephone)) {
            $this->error(1002);
        }

        $phonecount = preg_match("/1[3,4,5,7,8]{1}[0-9]{1}[0-9]{8}|0[0-9]{2,3}-[0-9]{7,8}(-[0-9]{1,4})?/", $mobilephone);
        if (!$phonecount) {
            $this->error(1002);
        }
    }


    //生成验证码并写入数据库
    /**
     * 生成验证码并写入数据库
     * @deprecated since version thrift_150709
     * @param type $mobilephone
     * @param type $ctype
     * @return type
     */
    protected function makeauthcode($mobilephone, $ctype) {
        /*
        $authcode = getauthcode();
        //ctype 1:注册；2：找回密码 3：更换已绑定手机；4：绑定新手机；5：绑定手机
        $data = array('mobilephone' => $mobilephone, 'authcode' => $authcode, 'add_time' => time(), 'ctype' => $ctype);

        $affectid = M('user_code')->add($data);
        if (!$affectid)
            $this->error(1003);

        return $authcode;
         */
    }


    //二维数组排序
    protected function array_sort($arr, $keys, $orderby = 'asc') {

        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }

        if ($orderby == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);

        foreach ($keysvalue as $k => $v) {
            $new_array[] = $arr[$k];
        }
        return $new_array;
    }

}