<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 * 臭美app发型 分组收藏视图 展示列表所用
 * @author andyyang
 * more: http://www.webyang.net
 */
/**
 * @deprecated since version thrift_150709
 */
class SalonItemTicketViewModel extends ViewModel {

	public $viewFields = array(
        'order_ticket' => array('order_ticket_id','status','ticketno','add_time','use_time','end_time','iscomment','_type'=>'LEFT'),
        'order_item'   => array('user_id','salonid','itemid','itemname','extra','price_dis','service_detail','_type'=>'LEFT','_on' => 'order_ticket.order_item_id=order_item.order_item_id'),
        'salon'        => array('salonname','commentnum','_on' => 'order_item.salonid=salon.salonid'),
	);

}
