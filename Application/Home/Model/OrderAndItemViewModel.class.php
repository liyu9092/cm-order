<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 *
 * @deprecated since version thrift_150709
 */
class OrderAndItemViewModel extends ViewModel {
	
	public $viewFields = array(
        'order' => array('ordersn','shopcartsn','priceall','salonid'=>'salonId','_as'=>'myorder'),
		'order_item' => array('order_item_id','itemid'=>'itemId','itemname'=>'itemName','num'=>'nums','extra','useLimit','_on' => 'myorder.ordersn=order_item.ordersn')
	);

}
