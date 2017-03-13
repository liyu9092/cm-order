<?php

namespace Home\Controller;
use Think\Log;
use Think\Controller;

class FixUserTicketController extends Controller
{
    
    /***
     * 修复用户支付后没有臭美券问题,补发臭美券
     * 
     * 注意：只包含第三方支付的订单没有生成臭美券的，才能做此操作。（包含代金券或余额的请单独使用sql修复）
     */
    public function ticketadd() {

        $str = $_GET['ordersn'];
        $arr = explode(',',$str);

        if(!$arr)
            exit('data err');
        $message = '';
        //print_r($arr);exit;
        foreach($arr as $val) {
            $ordersn = trim($val);
            //1. 首先根据订单号查询支付日志，不存在不执行补发臭美券流程。
            $paymentLogRes = M('payment_log')->where(array('ordersn' => $ordersn))->find();
            if(!$paymentLogRes){
                $message .= "ordersn:{$ordersn} has no paymentLog .<br/>\r\n";
                continue;
            }
            $payTime = $paymentLogRes['add_time'];
            $payId = $paymentLogRes['payid'];
            if($payId == 1){
                $pay_type = 2;
            }else if($payId == 2) {
                $pay_type = 3; 
            }
            //2. 根据订单号查询订单项目
            $orderInfo = M('order')->where(array('ordersn' => $ordersn))->find();
            if(!$orderInfo) {
                $message .= "ordersn:$ordersn is not exist in order table,exit <br/>\r\n";
                continue;
            }
            if($orderInfo['actuallyPay'] != $orderInfo['priceall']){
                $message .= "ordersn:$ordersn has used voucher,can't be fixed by this script,exit <br/>\r\n";
                continue;
            }
            
            //3. 根据订单号查询订单项目
            $orderItemInfo = M('order_item')->where(array('ordersn' => $ordersn))->find();
            if(!$orderItemInfo) {
                $message .= "ordersn:$ordersn is not exist in order_item table ,exit <br/>\r\n";
                continue;
            }
            
            //4. 查询是否已经有了臭美券了
            $order_ticket = M('order_ticket')->where(array('order_item_id' => $orderItemInfo['order_item_id']))->find();
            if($order_ticket) {
                $message .= "ordersn:$ordersn already has order_ticket,exit <br/>\r\n";
                continue;
            }
            
            //5. 查询一条可以使用的臭美券
            $ticketInfo = M('seed_pool')->where(array('STATUS' => 'NEW','TYPE' => 'TKT'))->find();
            if(!$ticketInfo) {
                $message .= "ordersn:$ordersn can't find the ticket from seed_pool table,exit <br/>\r\n";
                continue;
            }
            
            //6. 操作执行事务
            $model = M('order');
            $model->startTrans();                      
            //6.1. 修改订单号为已支付
            $updateOrderIsPayRes = $model->where(array('ordersn' => $ordersn))->save(array('ispay' => 2,'pay_time' => $payTime));
            if($updateOrderIsPayRes === false){
                $model->rollback();
                $message .= "ordersn:$ordersn update order table failed,exit <br/>\r\n";
                continue;
            }
            
            //6.2 更新臭美池中取出的臭美券的状态为已使用
            $updateSeedRes = M('seed_pool')->where(array('SEED' => $ticketInfo['SEED'],'STATUS' => 'NEW'))->save(array('STATUS' => 'USD'));
            if($updateSeedRes === false){
                $model->rollback();
                $message .= "ordersn:$ordersn update seed pool table failed,exit <br/>\r\n";
                continue;
            }
            
            //6.3 插入臭美券记录
             $insertTicketInfo = array(
                'otOrdersn' => $ordersn,
                'order_item_id' => $orderItemInfo['order_item_id'],
                'ticketno' => $ticketInfo['SEED'],
                'status' => 2,
                'iscomment' => 1,
                'use_time' => 0,
                'add_time' => $payTime,  //目前就写上支付时间为臭美券生成时间
                'end_time' => 0,
                'user_id' => $orderInfo['user_id'],
                'otSalonId' => $orderInfo['salonid'],
                'IsSendPhoneMsg' => 0
            );
            $insertTicketRes = M('order_ticket')->add($insertTicketInfo);
            if($insertTicketRes === false){
                $model->rollback();
                $message .= "ordersn:$ordersn insert into order_ticket table failed,exit <br/>\r\n";
                Log::write("insert into order_ticket table failed". print_r($insertTicketInfo,true));
                continue;
            }
            $model->commit();
            
            //7. 写支付流水
            $insertFundflowInfo = array(
                'record_no' => $ordersn,
                'ticket_no' => $ticketInfo['SEED'],
                'user_id' => $orderInfo['user_id'],
                'money' => $orderInfo['actuallyPay'],
                'pay_type' => $pay_type,
                'salonid' => $orderInfo['salonid'],
                'code_type' => 2,
                'add_time' => $payTime
            );
            $insertFundflowRes = M('fundflow')->add($insertFundflowInfo);
            if($insertFundflowRes === false){
                $message .= "ordersn:$ordersn insert into fundflow table failed,exit <br/>\r\n";
                Log::write("insert into fundflow table failed". print_r($insertFundflowInfo,true));
            }
            
            //8. 写臭美券动态记录
            $insertOrderTicketTrendsInfo = array(
                'ordersn' => $ordersn,
                'ticketno' => $ticketInfo['SEED'],
                'add_time' => time(),
                'status' => 2 ,//未使用
                'remark' => '未使用'
            );
            $insertOrderTicketTrendsRes = M('order_ticket_trends')->add($insertOrderTicketTrendsInfo);
            if($insertOrderTicketTrendsRes === false){
                $message .= "ordersn:$ordersn insert into order_ticket_trends table failed,exit <br/>\r\n";
                Log::write("insert into order_ticket_trends table failed". print_r($insertOrderTicketTrendsInfo,true));
            }
            
        }
        echo $message;
        echo "finish \r\n";
        exit;
    }
    
}
