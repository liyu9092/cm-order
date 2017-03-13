<?php
/**
 * model基类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class SalonFundflowModel extends Model{

    public function updateSalonFundflowByOrderSn($bountySn, $salonId, $bargainno) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updateSalonFundflowByOrderSn', array($bountySn, $salonId, $bargainno));
        return $return;
    }

}
