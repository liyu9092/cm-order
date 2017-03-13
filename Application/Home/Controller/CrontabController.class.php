<?php

namespace Home\Controller;
use Think\Log;

if(!IS_CLI)
    exit("this script could only run in cli mode");

class CrontabController extends BaseController
{
    

    public function _initialize() 
    {
        # 不允许外部访问
        if(!IS_CLI)
            exit("this script could only run in cli mode");
    }
    
    /**
     * 
     * 已抢任务不可展示在造型师端
     */
    public function updatebountyPushReqStatus()
    {
        /* 定时器设置的每分钟跑一次，这里预留一分钟 */
        $endtime = time();
        $starttime = $endtime-60*2;
        if($_SERVER['argv']['2'] == 'all')
            $endtime = $selecttime = '';
        else
        {
            $selecttime = " and selectTime<={$endtime} and selectTime>={$starttime}";
            $endtime = " and endTime<={$endtime} and endTime>={$starttime}";
        }
        $selectedTasks = M('bounty_task')->where("btStatus=2 {$selecttime}")->select();
        if($selectedTasks === false)
            $this->error(2, M('bounty_task')->GetError());
        $canceledTasks = M('bounty_task')->where("btStatus=9 {$endtime}")->select();
        if($canceledTasks === false)
            $this->error(2, M('bounty_task')->GetError());
        
        $reqObj = M("bounty_push");
        foreach($canceledTasks as $task)
        {
            #已取消任务，未抢单更新为未抢单且未选中
            $reqObj->where(array('btSn' => $task['btSn'], 'reqStatus' => 0))->save(array("reqStatus" => 2));
            #已取消任务，已抢单更新为已抢单且未选中
            $reqObj->where(array('btSn' => $task['btSn'], 'reqStatus' => 1))->save(array("reqStatus" => 5));
        }
        foreach($selectedTasks as $task)
        {
            #用户已制定造型师任务，未抢单更新为未抢单且未选中
            $reqObj->where(array('btSn' => $task['btSn'], 'reqStatus' => 0))->save(array("reqStatus" => 2));
            #用户已制定造型师任务，已抢单且被选中
            $reqObj->where(array('btSn' => $task['btSn'], 'reqStatus' => 0, 'stylistId' => $task['hairstylistId']))->save(array("reqStatus" => 3));
            #用户已制定造型师任务，已抢单更新为已抢单且用户选了其他造型师
            $reqObj->where(array('btSn' => $task['btSn'], 'reqStatus' => 1))->save(array("reqStatus" => 4));
        }
            
    }
    
    /**
     * 项目过期提醒(短信)
     */
    public function ticketExpireRemindSms(){
        $startTime = strtotime(date("Y-m-d"));
        $endTime = $startTime+4*24*3600-60;
        
        //查询order_ticket表中带有有效期的臭美券，过期时间还有三天
        $status = 2;
        $page = 0;
        $pageSize = 500;
        do{
            $ticketInfo =  D('orderTicket')->getOrderTicketByStatus($status,$endTime,$page,$pageSize);
            if($page > 2){
                Log::write("项目即将过期数量超过1000条，不发送短信");
                exit;
            }else{
                if($ticketInfo){
                    foreach($ticketInfo as $key => $ticketInfoValue){
                        $userId = $ticketInfoValue['userId'];
                        $orderInfo = D('orderItem')->getOrderInfoById($ticketInfoValue['orderItemId']);
                        $salonId = $orderInfo['salonid'];
                        $itemName = $orderInfo['itemname'];
                        //根据salonId获取salonName
                        $salonName = D('salon')->getSalonNameById($salonId);
                        //根据用户id获取手机号
                        $userInfo = D('User')->getUserById($userId);
                        $mobilephone = $userInfo['mobilephone'];
                        $smsText = C('SMS_D');
                        $repalceArray = array(
                            'salonName' => $salonName,
                            'itemName' => $itemName,
                        );
                        $smsText = D('Sms')->repalceSms($smsText,$repalceArray);
                        D('Sms')->sendSmsByType($mobilephone, $smsText,5);
                    }
                    $page++;
                }
                
            }
        }while(count($ticketInfo) >= $pageSize);
        
     
    }

    /**
     * 项目过期提醒(消息推送)
     */
    public function ticketExpireRemindPushMessage(){
        $startTime = strtotime(date("Y-m-d"));
        $endTime = $startTime+4*24*3600-60;
        
        //查询order_ticket表中带有有效期的臭美券，过期时间还有三天
        $status = 2;
        $page = 0;
        $pageSize = 500;
        do{
            $ticketInfo =  D('orderTicket')->getOrderTicketByStatus($status,$endTime,$page,$pageSize);
            if($ticketInfo){
                foreach($ticketInfo as $key => $ticketInfoValue){
                    $userId = $ticketInfoValue['userId'];
                    $ticketNo = $ticketInfoValue['ticketno'];
                    $orderInfo = D('orderItem')->getOrderInfoById($ticketInfoValue['orderItemId']);
                    $salonId = $orderInfo['salonid'];
                    $itemName = $orderInfo['itemname'];
                    //根据salonId获取salonName
                    $salonName = D('salon')->getSalonNameById($salonId);

                    $user = D('User')->getUserById($userId);
                    $user['os_type'] = $user['osType'];
                    $appType = '';
                    if($user['os_type'] == 1)   //Android
                        $appType="android";
                    elseif($user['os_type'] == 2) //Ios
                        $appType="ios";
                    $title = '项目过期提醒';
                    $desc = "您在{$salonName}购买的项目{$itemName}还有3天就过期了，请及时去体验美发服务吧。";
                    $payload = array(
                        'msgType' => 2,
                        'title' => $title,
                        'desc' => $desc,
                        'data' => array(
                            'ticketNo' => $ticketNo,
                            'event' => 'ticketInfo',
                            'msgType' => 2,
                        ),
                    );
                    $data = array(
                        'title' => $title,
                        'desc' => $desc,
                        'payload' => json_encode($payload),
                        'notifyForeground' => 1,
                        'passThrough' => 1,
                        'appType'=>$appType,
                        'badge' => 1,
                        'app' => 'v1',
                    );
                    $targetList = array(D('Des')->encrypt($userId));
                    //将推送的数据存入数据库
                    $event = json_encode($payload['data']);
                    $pushData = array(
                        array(
                            'receiverUserId' => $userId,
                            'type' => 'USR',
                            'osType' => strtoupper($appType),
                            'title' => $title,
                            'message' => $desc,
                            'priority' => 1,
                            'event' => $event,
                            'status' => 'SNT',
                            'createTime' => time(),
                            'updateTime' => time(),
                        )  
                    );
                    $res = D('Comment')->addPushs($pushData);
                    if($res){
                        if(!empty($appType)){
                            D("Push")->sendToAliases($data,$targetList);     
                        }  else {
                            Log::write("用户手机设备未能识别，无法推送,用户id:".$userId);
                        }
                        
                    }else{
                        Log::write("推送消息数据插入失败，臭美券号：".$ticketNo);
                    }

                }    
                $page++;
            }
        }while(count($ticketInfo) >= $pageSize);
            
        
    }
    
    /***
     * 代金券到期提醒
     */
    public function voucherExpireRemindPushMessage(){
        $startTime = strtotime(date("Y-m-d"));
        $endTime = $startTime+4*24*3600-1;
        //查询voucher表中带有有效期的代金券，过期时间还有三天
        $page = 0;
        $pageSize = 500;
        do{
            //注意两个时间都是endtime
            $voucherInfo = D('voucher')->getVouchersExpiring($endTime,$endTime,$page,$pageSize);
            if($voucherInfo){
                foreach ($voucherInfo as $key => $voucherInfoValue) {
                    $userId = $voucherInfoValue['vUserId'];
                    $mobilephone = $voucherInfoValue['vMobilephone'];
                    $voucherMoney = $voucherInfoValue['vUseMoney'];
                    $vSn = $voucherInfoValue['vSn'];

                    $user = D('User')->getUserById($userId);
                    $user['os_type'] = $user['osType'];
                    $appType = '';
                    if($user['os_type'] == 1)   //Android
                        $appType="android";
                    elseif($user['os_type'] == 2) //Ios
                        $appType="ios";
                    $title = '代金券即将过期';
                    $desc = "您有一张价值￥{$voucherMoney}的代金券，还有3天就要过期了，赶快去消费吧(点击查看详情)。";
                    $payload = array(
                        'msgType' => 3, //代金券即将过期
                        'title' => $title,
                        'desc' => $desc,
                        'data' => array(
                            'userId' => $userId,
                            'event' => 'voucherList',
                            'msgType' => 3,
                        ),
                    );
                    $data = array(
                        'title' => $title,
                        'desc' => $desc,
                        'payload' => json_encode($payload),
                        'notifyForeground' => 1,
                        'passThrough' => 1,
                        'appType'=>$appType,
                        'badge' => 1,
                        'app' => 'v1',
                    );
                    $targetList = array(D('Des')->encrypt($userId));
                    //将推送的数据存入数据库
                    $event = json_encode($payload['data']);
                    $pushData = array(
                        array(
                            'receiverUserId' => $userId,
                            'type' => 'USR',
                            'osType' => strtoupper($appType),
                            'title' => $title,
                            'message' => $desc,
                            'priority' => 1,
                            'event' => $event,
                            'status' => 'SNT',
                            'createTime' => time(),
                            'updateTime' => time(),
                        )  
                    );
                    $res = D('Comment')->addPushs($pushData);
                    if($res){
                        if(!empty($appType)){
                            D("Push")->sendToAliases($data,$targetList);   
                        }else{
                            Log::write("用户手机设备未能识别，无法推送,用户id:".$userId);
                        }
                          
                    }else{
                        Log::write("推送消息数据插入失败，代金券号：".$vSn);
                    }
                }
                $page++;
            }
            
        }while(count($voucherInfo) >= $pageSize);
        
    }


}
