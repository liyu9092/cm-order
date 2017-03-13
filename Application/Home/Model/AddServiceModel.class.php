<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

use Home\Model\BaseModel;

class AddServiceModel extends BaseModel {

    public function getAddServicesById($serviceIds)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('seller-center', 'getAddServicesById', array($serviceIds));
    }
    
    
}






