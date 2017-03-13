<?php
/**
 * 订单内部接口 公共控制器
 *
 * @author: carson
 */
namespace Home\Controller;

class OrderInController extends BaseController {
    protected $userId;
    protected $notNeedCheck=array(
        'Login'=>array('index'),
        'Sms'=>array('getAuthCode'),
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
    }


    private function parseToken(){
        $ctlName=$this->code['type'];
        $actName=$this->code['to'];
        //echo $ctlName..
        if(isset($this->notNeedCheck[$ctlName])){
            $funcArr=$this->notNeedCheck[$ctlName];
            if(empty($funcArr)){
                return true;
            }
            if(in_array($actName,$funcArr)){
                return true;
            }
        }
        $userIdStr=$this->param['userId'];
        $userId=D('Des')->decrypt($userIdStr);
        if(!$userId){
            $this->error(1,'001');
        }
        $this->userId=$userId;
    }
}
