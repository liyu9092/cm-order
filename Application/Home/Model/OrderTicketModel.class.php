<?php
/**
 * 臭美券处理类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class OrderTicketModel extends BaseModel {

    /**
     * 获取臭美劵号
     * @return string
     */
    public function getticketno() {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getTicketNo', array());
        
        /*
        $OrderTicketTempObj = M('order_ticket_temp');
        $CouponOrderTicketTempObj = M('coupon_order_ticket_temp');

        $ticketno_status = $CouponOrderTicketTempObj->getField('ticketno_status'); //查询当前起始值
        //下一个起始值
        $ticketno_status_next = $ticketno_status+1; //max 999

        /*$count = $OrderTicketTempObj->field('MAX(order_ticket_temp_id) maxId,MIN(order_ticket_temp_id) minId')->where("status = '$ticketno_status'")->find();
        if(!$count['maxId'] || !$count['minId']) {
            $CouponOrderTicketTempObj->where("coupon_order_ticket_temp_id = 1")->setField('ticketno_status',$ticketno_status_next); //起始值进一位
        }
        $noInfo=getNoInfo($ticketno_status,$count['minId'],$count['maxId']);*/

        /*
        $noInfo=$this->toGetNoInfo($ticketno_status);
        if(!$noInfo) {
            $CouponOrderTicketTempObj->where("coupon_order_ticket_temp_id = 1")->setField('ticketno_status',$ticketno_status_next); //起始值进一位
            return $this->getticketno();
        }
        //echo $OrderTicketTempObj->_sql().'<br>';
        //die();

        $affectid = $OrderTicketTempObj->where('order_ticket_temp_id='.$noInfo['order_ticket_temp_id'])->save(array('status'=>$ticketno_status_next));
        if($affectid) {
            $code = $ticketno_status.$noInfo['ticketno'];
            $ticketCount = $this->where("ticketno = $code")->count();
            if(!$ticketCount) {
                return $code;
            } else {
                //echo 2;
                return $this->getticketno();
            }
        } else {
            //echo 3;
            return $this->getticketno();
        }
        */
        
    }

    /**
     * 取得券号临时值信息 【方式1】
     * @param $ticketno_status
     * @return mixed
     */
    private function toGetNoInfo($ticketno_status){
        /*
        $OrderTicketTempObj = M('order_ticket_temp');
        $sql='SELECT * FROM cm_order_ticket_temp AS r1
JOIN (SELECT ROUND(RAND()*((SELECT MAX(order_ticket_temp_id) FROM cm_order_ticket_temp where `status`='.$ticketno_status.')-(SELECT MIN(order_ticket_temp_id) FROM cm_order_ticket_temp where `status`='.$ticketno_status.'))) AS tempId) AS r2
WHERE `status`='.$ticketno_status.' and r1.order_ticket_temp_id >= r2.tempId ORDER BY r1.order_ticket_temp_id ASC LIMIT 1;';
        list($noInfo)=$OrderTicketTempObj->query($sql);

        return $noInfo;
         */
    }

    /**
     * 取得券号临时值信息 【方式2】
     * @param $ticketno_status
     * @return mixed
     */
    private function getNoInfo($noStatus,$min,$max){
        /*
        $sb=$min+1000;
        $tempId=rand($min,$sb);
        $tempNoWhere['status']=$noStatus;
        $tempNoWhere['order_ticket_temp_id']=$tempId;
        $noInfo = M('order_ticket_temp')->where($tempNoWhere)->find();
        if(!$noInfo){
            echo 1;
            return $this->getNoInfo($noStatus,$min,$max);
        }
        return $noInfo;
         */
    }


    /****
    * 我的退款臭美卷信息列表
    * @params $userId int
    ****/
    public function getRefundList( $userId , $start , $pageSize ){
        
        /*
        $where = " user_id = %d and status in (6,7,8) ";
        $condition = array( $userId );
        $fields = array( "order_item_id" , "ticketno" , "end_time" ,"status");
        $order = " add_time desc ";
        $return = $this->field( $fields )->where( $where , $condition )->order( $order )->limit($start,$pageSize)->select();
        return $return;
         */
        $thrift = D('ThriftHelper');
        $tickets = $thrift->request('trade-center', 'getRefundList', array($userId, $start, $pageSize));
        if(empty($tickets))
            return null;
        $ret = array();
        foreach($tickets as $ticket)
        {
            $ret[] = array(
                "order_item_id" => $ticket['orderItemId'],
                "ticketno" => $ticket['ticketno'], 
                "end_time" => $ticket['endTime'],
                "status" => $ticket['status']
            );
        }
        return $ret;
    }


    /****
    * 我的退款臭美卷信息条数
    * @params $userId int
    ****/
    public function getRefundCount( $userId ){
        
        /*
        $where = " user_id = %d and status in (6,7,8) ";
        $condition = array( $userId );

        $return = $this->where( $where , $condition )->count();
        return $return;
         */
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getRefundCount', array($userId));
    }


    /****
    * 我的未消费臭美卷信息列表
    * @params $userId int
    ****/
    public function getNonConsumeList( $userId , $start , $pageSize ){

        /*
        $where = " user_id = %d and status = 2 ";
        $condition = array( $userId );
        $fields = array( "order_item_id" , "ticketno" , "end_time","status" );
        $order = " add_time desc ";
        $return = $this->field( $fields )->where( $where , $condition )->order( $order )->limit($start,$pageSize)->select();
        return $return;
         */
        
        $thrift = D('ThriftHelper');
        $tickets = $thrift->request('trade-center', 'getNonConsumeList', array($userId, floor($start/$pageSize) , $pageSize));
        if(empty($tickets))
            return null;
        $ret = array();
        foreach($tickets as $ticket)
        {
            $ret[] = array(
                "order_item_id" => $ticket['orderItemId'],
                "ticketno" => $ticket['ticketno'], 
                "end_time" => $ticket['endTime'],
                "status" => $ticket['status']
            );
        }
        return $ret;
    }


    /****
    * 我的未消费臭美卷信息条数
    * @params $userId int
    ****/
    public function getNonConsumeCount( $userId ){

        /*
        $where = " user_id = %d and status = 2 ";
        $condition = array( $userId );

        $return = $this->where( $where , $condition )->count();
        return $return;
         */
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getNonConsumeCount', array($userId));
    }



    /****
    * 我的退款臭美卷信息
    * @params $ticketNo string
    ****/
    public function getRefundInfo( $ticketNo ,$userId ){

        /*
        $where = " ticketNo = '%s' and user_id = %d ";
        $condition = array( $ticketNo , $userId );
        $fields = array( "order_item_id" , "ticketno" , "end_time" ,"status");
        $return = $this->field( $fields )->where( $where , $condition )->find();
        return $return;
         */
        
        $ticket = $this->getTicketByNo($ticketNo);
        if($ticket['userId'] != $userId)
            return null;
        
        $ret = array(
            "order_item_id"=>$ticket['orderItemId'], 
            "ticketno"=>$ticketNo,
            "end_time"=>$ticket['endTime'], 
            "status" => $ticket['status'],
            );
        return $ret;
    }
    
    /*****
     * 根据臭美券号获取订单号
     */
    public function getOrderSnByTicketNo($ticketNo){
        /*
        $where1 = array(
            'ticketno' => $ticketNo
        );
        $order_item_id = $this->where($where1)->getField('order_item_id');
        //通过项目id获取订单号
        $where2 = array(
            'order_item_id' => $order_item_id 
        );
        $orderSn = M('order_item')->where($where2)->getField('ordersn');
        return $orderSn;
        */
        $ticket = $this->getTicketByNo($ticketNo);
        $orderItem = D('OrderItem')->getItemById($ticket['orderItemId']);
        return $orderItem['ordersn'];
    }
    
    /**
     * 我的臭美券列表
     */
    public function getUserTicketNoList($user_id) {
        /*
        $where = 'user_id= %d';
        $condition = array($user_id);
        $fields = array("ticketno");
        $return =  $this->field( $fields )->where( $where , $condition )->order('add_time')->select();
        return $return;
         */
      
    }
    
    //通过userId查询ticketNO
    public function getTicketNoByUserId($user_id)
    {
        /*
        return M('order_ticket')->field('ticketno')->where('user_id= %d',$user_id)->order('add_time')->find();
         */
        /*
        $thrift = D('ThriftHelper');
        $ticketNo = $thrift->request('trade-center', 'getTicketNoByUserId', array($user_id));
        if(empty($ticketNo)) return null;
        else return array('ticketno' => $ticketNo);
         */
        
        $tickets = $this->getTicketByUserId($user_id, 0, 0, 1, 1);
        if(empty($tickets) || empty($tickets[0]) || empty($tickets[0]['ticketno'])) return null;
        else return array('ticketno' => $tickets[0]['ticketno']);
    }
    
    /**
     * 获取用的臭美券列表
     * @param type $userId
     * @param type $status
     * @param type $page
     * @param type $size
     * @param type $sortType 0=>addTime desc, 1=>addTime asc
     * @return type
     */
    public function getTicketByUserId($userId, $status, $page, $size, $sortType = 0)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getTicketByUserId', array($userId, $status, $page, $size, $sortType));
    }
    
    public function getTicketNumByUserId($userId, $status)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getTicketNumByUserId', array($userId, $status));
    }
    
    public function getTicketByNo($ticketNo)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getTicketByNo', array($ticketNo));
    }
    
    public function getTicketByOrderItemId($orderItemId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getTicketByOrderItemId', array($orderItemId));
    }
    
    public function updateStatus($ticketNo, $status)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateTicketStatus', array($ticketNo, $status));
    }
    
    public function addOrderTicket($orderItemId, $orderSn, $userId, $ticketNo, $endTime)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'addOrderTicket', array($orderItemId, $orderSn, $userId, $ticketNo, $endTime));
    }
    
    public function updateTicketIsComment($orderTicketId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateTicketIsComment', array($orderTicketId));
    }
    
    public function updateTicketSendMsgStatus($ticketNo)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateTicketSendMsgStatus', array($ticketNo));
    }
    
    public function getOrderTicketByStatus($status, $endTime, $page, $pageSize)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getOrderTicketByStatus', array($status, $endTime,$page, $pageSize));
    }
}


