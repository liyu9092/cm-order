<?php
/****
 * @author lufangrui
 * @desc 获取相应规格名称
 * @time 2015-04-14
 ****/

namespace Home\Model;
use Think\Model;

class SalonItemFormatModel extends BaseModel {
    /****
     * 通过规格id来找到对应的规格名称
     * @params $formatId int
     * @deprecated 
     ****/
    public function getItemForamtName( $formatId ){
        /*
        $where = " salon_item_format_id = %d ";
        $condition = array( $formatId );
        $fields = array( "format_name" , "salon_item_formats_id" );

        $return = $this->field( $fields )->where( $where,$condition )->find();
        return $return;
         */
    }
}