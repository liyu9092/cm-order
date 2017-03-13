<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

class SalonItemTypeModel extends BaseModel {
    /*
     * 获取所有项目类型
     */

    public function getType($voucher = false) {
        
        $thrift = D('ThriftHelper');
        $itemTypes = $thrift->request('seller-center', 'getItemTypes', array());
        if(empty($itemTypes))
            return null;
        $ret = array();
        foreach($itemTypes as $type)
        {
            if($voucher && in_array($type['typeid'], array(6, 10))) continue;
            
            $ret[$type['typeid']] = $type['typename'];
        }
        return $ret;
        
        /*
        if ($voucher) {
            $type = M("salon_itemtype")->where(array('typeid' => array('not in', array('6', '10'))))->select();
        } else {
            $type = M("salon_itemtype")->select();
        }
        $itemType = array();
        foreach ($type as $val) {
            $itemType[$val['typeid']] = $val['typename'];
        }
        return $itemType;
         */
    }
    
    public function getTypeName($typeid)
    {
        return $this->getType()[$typeid];
    }

}
