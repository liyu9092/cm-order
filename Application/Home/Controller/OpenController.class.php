<?php
/**
 * 开放接口
 *
 * @author: carson
 */
namespace Home\Controller;
use Think\Controller;

class OpenController extends Controller {

    public function disposeActivityCode(){
        $thesn=$_GET['mysn'];
        $mchId=$_GET['mchId'];
        if(!$thesn || !is_numeric($thesn)){
            exit('1');
        }
        $CodeActivityObj=D('CodeActivity');
        if($mchId){
            $datas=['mchId'=>$mchId];
            $CodeActivityObj->setOtherData($datas);
        }
        $len=strlen($thesn);
        if($len==13){
            $CodeActivityObj->disposeOrder($thesn);//处理激活码的东西
        }else if($len==20){
            $where['shopcartsn']=$thesn;
            $orderInfo=M('order')->where($where)->order('orderid asc')->find();
            if(!$orderInfo){
                exit('2');
            }
            $CodeActivityObj->disposeOrder($orderInfo['ordersn'],$thesn);//处理激活码的东西
        }
        exit('ok');
    }

    public function test(){
        return false;
        $CodeActivityObj=D('CodeActivity');
        $datas=['mchId'=>1243472202];
        $CodeActivityObj->setOtherData($datas);
        $ret=$CodeActivityObj->sendToWeChatRedPacket('1007350799201507290502534960',100,'sb');
        print_r($ret);
    }
}
