<?php
/****
* @author lufangrui
* @desc 获取项目信息
* @time 2015-04-14
****/
namespace Home\Model;

class SalonItemModel extends BaseModel {
    /****
    * 根据项目id获取项目名及其信息
    ****/
    public function getItemInfo( $itemId ){
        $item = $this->getItemById($itemId);
        if(empty($item))
            return null;
        return array("typeid" => $item['typeid'], "itemname" => $item['itemname'], 'salonid' => $item['salonid'], 'item_type' => $item['itemType'],
		 'addserviceStr' => $item['addserviceStr'], 'useLimit' => $item['useLimit']);
        /*
        $where = " itemid = %d ";
        $condition = array( $itemId );
        $fields  = array( "salonid" , "typeid" ,"itemname" ,"item_type","addserviceStr","useLimit" );

        $return = $this->field( $fields )->where( $where , $condition )->find();
        return $return;
         */
    }
    /****
    * 根据项目id获取项目logo
    ****/
    public function getItemLogoAndType( $itemId ){
        $item = $this->getItemById($itemId);
        if(empty($item))
            return null;
        return array("logo" => $item['logo'], "typeid" => $item['typeid'], 'useLimit' => $item['useLimit'], 'item_type' => $item['itemType']);
        /*
        $where = " itemid = %d ";
        $condition = array( $itemId );
        $fields  = array( "logo" , "item_type","useLimit","typeid" );

        $return = $this->field( $fields )->where( $where , $condition )->find();
        return $return;
         */
    }
    
     /****
    * 根据项目id和salonId获取项目
    ****/
    public function getItem($itemId,$salonId) {
        $item = $this->getItemById($itemId);
        if($item['status'] != 1 || $item['salonid'] != $salonId)
            return null;
        return array("itemid" => $item['itemid'], "itemname" => $item['itemname']);
        /*
        $where = "status = 1 and itemid = %d and salonid = %d ";
        $condition = array($itemId,$salonId);
        $fields  = array( "itemid" , "itemname" );
        $return = $this->field($fields)->where($where,$condition)->find();
       // echo $this->getLastSql();
        return $return;
         */
    }
    
    public function getItemById($itemId)
    {
        $thrift = D('ThriftHelper');
        $item = $thrift->request('seller-center', 'getItemInfo', array($itemId));
        return $item;
    }
    
    public function updateComment($itemId, $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep)
    {
        $thrift = D('ThriftHelper');
        $ret = $thrift->request('seller-center', 'updateItemComment', array($itemId, $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep));
        return $ret;
    }
    
    public function updateItemRepertory($itemId, $num, $innage)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('seller-center', 'updateItemRepertory', array($itemId, $num, $innage));
    }
}