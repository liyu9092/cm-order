<?php
namespace Home\Model;
use Think\Model;

/**
 * des加密类
 * @author carson
 */
class DesModel{
    private $Des;   //类
    private $key;   //加密key

    public function __construct(){
        $this->getRandKey();
        $this->Des=new \Think\NetDesCrypt();
        $this->Des->setKey($this->key);
    }


    private function getRandKey(){
        $this->key='authorlsptime20141225';
    }


    public function encrypt($str){
        return $this->Des->encrypt($str);
    }


    public function decrypt($str){
        return $this->Des->decrypt($str);
    }
}