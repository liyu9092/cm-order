<?php
namespace Home\Model;
use Think\Model;

class OrderTicketTrendsModel extends Model {
    
    public function addOrderTicketTrends($orderSn, $ticketNo, $status, $rereason)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'addOrderTicketTrends', array($orderSn, $ticketNo, $status, $rereason));
    }
}
