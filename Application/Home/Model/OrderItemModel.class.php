<?php
/****
 * @author lufangrui
 * @desc 获取臭美卷生成的订单详情
 ****/

namespace Home\Model;
use Think\Model;

class OrderItemModel extends BaseModel {
    /****
     * 通过订单项目id获取项目订单详情
     ****/
    public function getOrderInfoById( $itemId ){
        
        /*
        $where = " order_item_id = %d ";
        $condition = array( $itemId );
        $fields = array( "extra","service_detail","priceall","priceall_ori","itemname","itemid","salonid","orderid" );

        $return = $this->field( $fields )->where( $where , $condition )->find();
        return $return;
         */
        $orderItem = $this->getItemById($itemId);
        if(empty($orderItem))
            return null;
        
        $return = array(
            'extra' => $orderItem['extra'],
            'itemid' => $orderItem['itemId'],
            'salonid' => $orderItem['salonId'],
            'orderid' => $orderItem['orderId'],
            'ordersn' => $orderItem['ordersn'],
            'itemname' => $orderItem['itemname'],
            'priceall' => $orderItem['priceall'],
            'priceall_ori' => $orderItem['priceallOri'],
            'service_detail' => $orderItem['serviceDetail'],
            );
        return $return;
    }
    
    /***
     * 把orderItemData插入到order_item表中
     */
    public function  addOrderItem($orderId, $orderSn, $itemId, $userId, $salonId, $itemname, $num, $priceDis, $priceall, $priceallOri, $salonItemFormatId, $normsStr, $expTime, $desc, $useLimit, $salonNormsId) {
        $thrift = D('ThriftHelper');
//        $orderItemId = $thrift->request('trade-center', 'addOrderItem', array($orderId, $orderSn, $itemId, $userId, $salonId, $itemname, $num, $priceDis, $priceall, $priceallOri, $salonItemFormatId, $normsStr, $expTime, $desc, $useLimit, $salonNormsId));
            $param = new \cn\choumei\thriftserver\service\stub\gen\OrderItemParam();
            $param->orderId = $orderId;
	    $param->orderSn = $orderSn;
	    $param->itemId = $itemId;
            $param->userId = $userId;
	    $param->salonId = $salonId;
            $param->itemName = $itemname;
            $param->num = $num;
            $param->priceDis = $priceDis;
            $param->priceall = $priceall;
            $param->priceallOri = $priceallOri;
            $param->extra = $salonItemFormatId;  
            $param->normsStr = $normsStr;
            $param->endTime = $expTime;
            $param->serviceDetail = $desc;
            $param->useLimit = $useLimit;
            $param->salonNormsId = $salonNormsId; 
            $orderItemId =  $thrift->request('trade-center', 'addOrderItem', array($param));
        return $orderItemId;
        
//        return M('order_item')->add($orderItemData);
    }
    
    /***
     * 通过orderId拿到订单项目信息
     */
    public function getOrderItemByOrderId($orderId) {
        /*
        return M('order_item')->where(array('$orderid' => $orderId))->find();
         */
        $thrift = D('ThriftHelper');
        $orderItem = $thrift->request('trade-center', 'getOrderItemByOrderId', array($orderId));
        return $orderItem;
    }
    
    /**
     * 更新订单项目中的price
     */
    public function  updateOrderItemPrice($orderSn, $priceDis, $priceall) {
        /*
         $data['price_dis']=$priceDis;
         $data['priceall']=$priceDis;
         $oiRs = M('order_item')->where($where)->setField($data);
         */
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateOrderItemPrice', array($priceDis, $priceall, $orderSn));
    }
    
    /**
     * 通过订单项目id获取信息
     * @param type $orderItemId
     * @return type
     */
    public function getItemById($orderItemId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getOrderItemById', array($orderItemId));
    }
}
