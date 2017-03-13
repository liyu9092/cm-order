<?php
/**
 * 订单外部接口 入口
 *
 * @author: carson
 */
namespace Home\Controller;

class IndexController extends OrderOutController {


    public function index() {

        try{
            $MyCtrl = A($this->code['type']);
            $method = $this->code['to'];
            if(!method_exists($MyCtrl,$method)){
                exit('access failed~');
            }
            call_user_func(array($MyCtrl,$method));
            //$MyCtrl->$method();
        }catch (Exception $e){
            exit('access failed~');
        }
    }

}
