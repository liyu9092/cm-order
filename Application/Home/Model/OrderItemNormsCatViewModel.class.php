<?php
namespace Home\Model;
use Think\Model\ViewModel;


/**
 * @deprecated since version thrift_150709
 */
    class OrderItemNormsCatViewModel extends ViewModel {

    public $viewFields = array(
        'Order' => array(
            'orderid'=>'orderId',
            'ordersn'=>'orderSn',
            'status',
            'priceall'=>'priceAll',
            'priceall_ori'=>'priceOri',
            'add_time'=>'addTime',
            '_as'=>'cmOrder',
            '_type' => 'RIGHT'
        ),
        'Order_item' =>array(
            'itemid'=>'itemId',
            'normsStr'=>'norms',
            '_type' => 'RIGHT',
            '_on' => 'Order_item.orderid = cmOrder.orderId'
        ),
        'Salon_item' => array(
            'salonid'=>'salonId',
            'itemname'=>'itemName',
            'minPrice',
            'maxPrice',
            'minPriceOri',
            'maxPriceOri',
            'logo'=>'img',
            'useLimit',
            'total_rep',
            'status'=>'salonItemStatus',
            'sold',
            'item_type'=>'itemType',
            '_type' => 'LEFT',
            '_on' => 'Salon_item.itemid=Order_item.itemid'
        ),
        'Salon' => array(
            'salonname'=>'salonName',
            '_type' => 'LEFT',
            '_on' => 'Salon.salonid=Salon_item.salonId'
        ),
        'Salon_item_buylimit' => array(
            'limit_time'=>'limitNum',
            'limit_invite'=>'limitCode',
            'limit_first'=>'limitFirst',
            '_type'=>'LEFT',
            '_on'=>'Salon_item_buylimit.salon_item_id=Order_item.itemid'
        )
    );

}