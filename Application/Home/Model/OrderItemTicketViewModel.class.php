<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 臭美app发型 分组收藏视图 展示列表所用
 * @deprecated since version thrift_150709
 * @author andyyang
 * more: http://www.webyang.net
 */
class OrderItemTicketViewModel extends ViewModel {

	public $viewFields = array(
        'order_item'   => array('ordersn','salonid','itemname','price_dis','priceall','extra','itemid','useLimit','_type'=>'LEFT'),
        'order_ticket' => array('user_id','order_ticket_id','ticketno','status','_on' => 'order_ticket.order_item_id=order_item.order_item_id'),
	);

}
