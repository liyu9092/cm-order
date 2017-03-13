<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

use Home\Model\BaseModel;

class HairStylistModel extends BaseModel {
    protected $tableName = 'hairstylist';
    /***
     * 获取造型师基本信息
     * @param $stylistId 造型师id
     * @lufangrui
     * @2015-05-16
     ***/
    public function getHairstylist( $stylistId ){
        /*
          $fields = array(
          'stylistName','grade','stylistImg'
          );
          $where = ' stylistId = %d ';
          $condition = array( $stylistId );
          $return = $this->field( $fields )->where( $where,$condition )->find();
          return $return;
         */
        $thrift = D('ThriftHelper');
        $stylist = $thrift->request('seller-center', 'getStylistInfo', array($stylistId));
        return $stylist;
    }
    /***
     * 获取正常的造型师的数量
     */
    public function getHairstylistCount($status) {
        $thrift = D('ThriftHelper');
        $count = $thrift->request('seller-center', 'getHairstylistCount', array($status));
        return $count;
    }
}
