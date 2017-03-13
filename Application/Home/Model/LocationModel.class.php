<?php

namespace Home\Model;
use Think\Model;

class LocationModel extends Model {
    
    public function getTownsOfCity($city)
    {
        $thrift = D('ThriftHelper');
        $towns = $thrift->request('seller-center', 'getSubLocations', array(2, $city));
        if(empty($towns))
            return null;
        return $towns;
    }
    
}
?>