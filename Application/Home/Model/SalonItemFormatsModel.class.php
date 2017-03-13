<?php
/****
 * @author lufangrui
 * @desc 	获取子规格对应的父类规格
 * @time 2015-04-14
 ****/
namespace Home\Model;
use		Think\Model;
/**
 * @deprecated since version thrift_150709
 */
class SalonItemFormatsModel extends BaseModel {
    /****
     * 通过规格id来找到对应的规格名称
     * @params $formatId int
     ****/
    public function getItemForamtName( $formatId ){
        /*
        $where = " salon_item_formats_id = %d ";
        $condition = array( $formatId );
        $fields = array( "formats_name" );

        $return = $this->field( $fields )->where( $where,$condition )->find();
        return $return["formats_name"];
         */
    }
}