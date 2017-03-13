<?php

namespace Home\Controller;

class StylistController extends OrderOutController{
    
    protected $stylistId = null;
    protected $stylist = null;
    
    /**
     * 我的业绩
     */
    public function achievement() 
    {
        /*
        $stylist = M('hairstylist')->field(array('stylistImg'))->where("stylistId={$this->stylistId} and status=1")->find();
         */
        $stylist = D('HairStylist')->getHairstylist($this->stylistId);
        if(!$stylist)
            $this->error(2);
        
        /*
        $tasks = M('bounty_task')->field(array('money', 'satisfyType'))
                ->where(array('hairstylistId' => $this->stylistId, 'btStatus' => 4))->select();
         */
        $tasks = D('Bounty')->getStylistBountyTask($this->stylistId, 4, 0, 0, 9999);
        if($tasks === false)
            $this->error(2);
        
        $bounty = $satisfynum = $unsatisfynum = 0;
        foreach($tasks as $task)
        {
            if($task['satisfyType'] == 1)
            {
                $satisfynum++;
                $bounty+= $task['money'];
            }
            else 
                $unsatisfynum++;
        }
		
        $satisfy = round( $satisfynum / ($unsatisfynum+$satisfynum) * 100, 2);
	$satisfy = $satisfy . '%';
	$overNum = $unsatisfynum + $satisfynum;

        $main = array(
            'stylistImg'=> $stylist['stylistImg'],
            'bounty'    => $bounty,
            'satisfynum'=> $satisfynum,
            'unsatisfynum'=> $unsatisfynum,
	    'satisfy'=> $satisfy,
	    'overNum'=> $overNum,
        );
        
        $this->success(array('main' => $main));
    }

    /**
     * 任务申请
     */
    public function taskRequest() 
    {
        //无权接单
        if($this->stylist['grade'] == 0)
            $this->error(3012);
        
        $sn = $this->param['sn'] ?: $_REQUEST['sn'];
        $remark = $this->param['remark'] ?: $_REQUEST['remark'];
        $encoding = mb_detect_encoding($remark);
        $strlen = $encoding ? mb_strlen($remark, $encoding) : strlen($remark);
        #check input
        if(empty($sn) || $strlen > 20)
            $this->error(1);
        
        #can apply?
        /*
        $task = M('bounty_task')->where(array('btSn' => $sn))->find();
         */
        $task = D('Bounty')->getBountyTaskBybtSn($sn);
        if($task === false)
            $this->error(10);
        if(empty($task))
            $this->error(3002);
        
        #already pick someone else
        if(!empty($task['hairstylistId']) && $task['hairstylistId'] != $this->stylistId)
            $this->error(3006);
        
        /*
        $pushinfo = M('bounty_push')->where(array('btSn' => $sn, 'stylistId' => $this->stylistId))->find();
        if($pushinfo === false)
            $this->error(10);
         
        $pushs = D('BountyPush')->getStylistPushTask($this->stylistId, 0, time(), 0, 9999);
        if($pushs === false)
            $this->error(10);
        foreach($pushs as $push)
        {
            if($push['btSn'] != $sn)
                continue;
            $pushinfo = $push;
            break;
        }
        */
        
        $pushinfo = D('BountyPush')->getBountyPushInfo($sn,$this->stylistId);
        if($pushinfo === false)
           $this->error(10);
        #candidates?
        if(empty($pushinfo))
            $this->error(3007);
        
        #alread apply?
        /*
        $req = M('bounty_request')->where(array('btSn' => $sn, 'hairstylistId' => $this->stylistId))->find();
         
        $reqInfos = D('BountyRequest')->getBountyRequestList($sn, 0, 9999);
        if($reqInfos === false)
            $this->error(10);
        foreach($reqInfos as $reqInfo)
        {
            if($reqInfo['hairStylistId'] != $this->stylistId)
                continue;
            $req = $reqInfo;
            break;
        }
        */
        $req = D('BountyRequest')->getBountyRequest($sn,$this->stylistId);
        if($req === false)
             $this->error(10);
        if(!empty($req))
            $this->success();
            
        #apply
        /*
        $data = array(
            'btSn' => $sn,
            'hairstylistId' => $this->stylistId,
            'salonId' => $this->stylist['salonId'],
            'remark' => $remark ?: '',
            'brStatus' => 1,
            'addTime' => time(),
        );
        $result = M('bounty_request')->add($data);
         */
        $result = D('BountyRequest')->addBountyRequest($sn,  $this->stylist['salonId'], $this->stylistId, 1, time(), $remark);
        if($result <= 0)
            $this->error(2);
        #add task requestnum, if error, doesn't matter
        /*
        M('bounty_task')->where(array('btSn' => $sn))->setInc('requestNum');
        M('bounty_push')->where(array('btSn' => $sn, 'stylistId' => $this->stylistId))->save(array('reqStatus' => 1));
         */
        D('Bounty')->updateBountyTaskBybtSnAndSalonId($sn, 0, 0, 0, 1, 0, 0);
        D('BountyPush')->updateReqStatus($sn, 1, $this->stylistId);
            
        $this->success();
    }
    
    /**
     * 任务完成
     */
    public function taskFinish() 
    {
        $sn = $this->param['sn'] ?: $_REQUEST['sn'];
        
        if(empty($sn))
            $this->error(1);
        
        #can apply?
        /*
        $task = M('bounty_task')->where(array('btSn' => $sn))->find();
         */
        $task = D('Bounty')->getBountyTaskBybtSn($sn);
        if(empty($task))
            $this->error(3002);
        
        #already serv? 3 serv 4 pay 9 cancel
        if(in_array($task['btStatus'], array(3,4,9)))
            $this->success();
        
        #owner?
        if($task['hairstylistId'] != $this->stylistId)
            $this->error(3008);
        
        /*
        $result = M('bounty_task')->where(array('btSn' => $sn))->save(array('btStatus' => 3, 'serviceTime' => time()));
         */
        $result = D('Bounty')->stylistUpdateTask($sn, 3, 0, 0);
        if($result !== 1 && $result !== 0)
            $this->error(2);
        
        /*
        $user = M('user')->where(array('user_id' => $task['userId']))->find();
         */
        $user = D('User')->getUserById($task['userId']);
        $user['os_type'] = $user['osType'];
        \Think\Log::record("pushStylist user:".print_r($user, true));
        $appType = '';
        if($user['os_type'] == 1)   //Android
//            $url = C('PUSH_SERVICE_ANDROID');
            $appType="android";
        elseif($user['os_type'] == 2) //Ios
//            $url = C('PUSH_SERVICE_IOS');
            $appType="ios";
        $payload = array(
            'msgType' => 1,
            'title' => '服务完成',
            'desc' => '您有一单任务服务已完成，快去打赏造型师吧！',
            'data' => array(
                'bountySn' => $task['btSn'],
                'event' => 'taskFinish',
                'msgType' => 1,
            ),
        );
        $data = array(
            'title' => '服务完成',
            'desc' => '您有一单任务服务已完成，快去打赏造型师吧！',
            'payload' => json_encode($payload),
            'notifyForeground' => 1,
            'passThrough' => 1,
            'appType'=>$appType,
//            'targetList' => D('Des')->encrypt($task['userId']),
            'badge' => 1,
            'app' => 'v1',
//            'targetType' => 'alias',
        );
        $targetList = array(D('Des')->encrypt($task['userId']));
        \Think\Log::record("pushStylist data:".print_r($data, true));
        \Think\Log::record("pushStylist targetList:".print_r($targetList, true));
        //将推送的数据存入数据库
        $event = json_encode($payload['data']);
        $pushData = array(
            array(
                'receiverUserId' => $task['userId'],
                'type' => 'USR',
                'osType' => strtoupper($appType),
                'title' => '服务完成',
                'message' => '您有一单任务服务已完成，快去打赏造型师吧！',
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
                Log::write("用户手机设备未能识别，无法推送,用户id:".$task['userId']);
            }
            
        }else{
            Log::write("服务完成推送消息数据插入失败，臭美券号：".$ticketNo);
        }    
//        curlPost($url, $data);
        
        $this->success();
    }
    
    
    /**
     * 任务打分
     */
    public function taskScore() 
    {
        $sn = intval($this->param['sn']) ?: $_REQUEST['sn'];
        $score = intval($this->param['score']) ?: intval($_REQUEST['score']);
        
        if(empty($sn) || $score>5 || $score<1)
            $this->error(1);
        
        #can apply?
        /*
        $task = M('bounty_task')->where(array('btSn' => $sn))->find();
         */
        $task = D('Bounty')->getBountyTaskBybtSn($sn);
        if(empty($task))
            $this->error(3002);
        
        #owner?
        if($task['hairstylistId'] != $this->stylistId)
            $this->error(3008);
        
        #already score? consider done
        if($task['userScore']>0)
            $this->success();
        
        #not serv yet?
        if($task['btStatus'] != 4)
            $this->error(3009);
        
        /*
        $result = M('bounty_task')->where(array('btSn' => $sn))->save(array('userScore' => $score));
         */
        $result = D('Bounty')->stylistUpdateTask($sn, 0, 0, $score);
        if($result !== 1 && $result !== 0)
            $this->error(2);
        
        $this->success();
    }

    /**
     * 我的任务列表
     */
    public function tasks() 
    {
        $lastLoadFirstPageTime = intval($this->param['time']);
        $page = intval($this->param['p']);
        $page = $page>=1 ? ($page-1) : 0;
        $size = intval($this->param['size']);
        $size = $size>0 ? $size : 20;
        
        $time = $page == 0 ? time() : $lastLoadFirstPageTime;
        
        $statusarr = explode(',', $this->param['status']);
        foreach($statusarr as &$status)
            $status = intval($status);
        
        /*
        $where = "hairstylistId={$this->stylistId}";
        if($time && $this->param['status']==9)
            $where.= " and endTime<={$time}";
        elseif($time)
            $where.= " and selectTime<={$time}";
        
        if(count($statusarr) > 1) 
            $where.= ' and btStatus in (' .implode (',', $statusarr). ')';
        elseif(count($statusarr) == 1) 
            $where.= " and btStatus={$statusarr[0]}";
        
        $order = " addTime desc";          
        $tasks = M('bounty_task')->where($where)->order($order)->limit($page*$size, $size)->select();
         */
        $tasks = D('Bounty')->getStylistBountyTask($this->stylistId, implode(',', $statusarr), $time, $page, $size);
//        var_dump(M('bounty_task')->getlastsql());
        if($tasks === false)
            $this->error(2);
        if(empty($tasks) || !is_array($tasks))
            $this->success(array('main' => [], 'other' => array('serverTime' => $time, 'newtasknum' => 0)));
        
        
        $main = array();
        foreach($tasks as $task)
        {
            if($task['taskType'] == 2)
            {
                $detail = json_decode($task['detail'], true);
                $needsarr = array();
                foreach($detail as $each)
                {
                    if(empty($each['needsStr'])) continue;
                    
                    $needsarr = array_merge($needsarr, explode(',', $each['needsStr']));
                }
                $needs = implode(',', array_unique($needsarr));
                $madeto = '闺蜜X'.count($detail);
                $reason = null;
            }
            else
            {
                $needs = $task['needsStr'];
                $madeto = $this->madetostr($task['madeTo']);
                $reason = $task['reason'];
            }
            $remark = empty( $task['remark'] ) ? '' : $task['remark'] ;
            $needsStr = str_replace(',', '+', $needs);
            $main[] = array(
                "needs" => $needs,
                "money" => $task['money'],
                "sn" => $task['btSn'],   
                "status" => $task['btStatus'],
                "satisfy" => $task['satisfyType'],
                "type" => $task['taskType'],
                "score" => $task['userScore'],
                "addTime" =>$task['addTime'],
                "madeto" => $madeto,
                "reason" => $reason,
                "remark" => $remark,
                "needsStr"=>$needsStr
            );
        }
        
        if($page == 0)
        {
            /*
            $countwhere = "hairstylistId={$this->stylistId}";
            if($time && $this->param['status']==9)
                $countwhere.= " and endTime>{$lastLoadFirstPageTime}";
            elseif($time)
                $countwhere.= " and selectTime>{$lastLoadFirstPageTime}";

            if(count($statusarr) > 1) 
                $countwhere.= ' and btStatus in (' .implode (',', $statusarr). ')';
            elseif(count($statusarr) == 1) 
                $countwhere.= " and btStatus={$statusarr[0]}";
            $newtasknum = (int)M('bounty_task')->field('count(1) as total')->where($countwhere)->find()['total'];  
             */  
            $newtasknum = 0;
            foreach($statusarr as $status)
            {
                $newtasknum += D('Bounty')->getStylistNewBountyTaskNum($this->stylistId, $status, $lastLoadFirstPageTime);
            }
        }
        else
            $newtasknum = 0;
        
        return $this->success(array('main' => $main, 'other' => array('serverTime' => $time, 'newtasknum' => $newtasknum)));
    }
    
    public function taskDetail()
    {
        $sn = $this->param['sn'];
        if(empty($sn))
            $this->error(1);
        
        /*
        $task = M('bounty_task')->where(array('btSn' => $sn))->find();
         */
        $task = D('Bounty')->getBountyTaskBybtSn($sn);
        if(empty($task))
            $this->error(3002);     
        
        #requestinfo
        /*
        $req = M('bounty_request')->where(array('btSn' => $sn, 'hairstylistId' => $this->stylistId))->find();
//        var_dump($req, M('bounty_request')->getlastsql());
        if($req === false)
            $this->error(10);
         */
        $reqInfos = D('BountyRequest')->getBountyRequestList($sn, 0, 9999);
        if($reqInfos === false)
            $this->error(10);
        foreach($reqInfos as $reqInfo)
        {
            if($reqInfo['hairstylistId'] != $this->stylistId)
                continue;
            $req = $reqInfo;
            break;
        }

        #task info
        if($task['taskType'] == 2)
        {
            $detail = json_decode($task['detail'], true);
            foreach($detail as &$each)
            {
                $each['needs'] = $each['needsStr'];
                $each['needsStr'] = str_replace(',', '+', $each['needsStr']);
//                unset($each['needsStr']);
            }
        }
        else
            $detail = array(array('madeto'=> $this->madetostr($task['madeTo']),'reason'=> $task['reason'],'remark'=> $task['remark'],'name' => $task['name'], 'needs' => $task['needsStr'],'needsStr'=> str_replace(',', '+', $task['needsStr'])));
        
        $main = array(
            'money' => $task['money'],
            'sn'    => $task['btSn'],
            'status'=> $task['btStatus'],
            'satisfy' => $task['satisfyType'],
            'pay'   => $task['ispay'],
            'type'  => $task['taskType'],
            'detail'=> $detail,
            'score' => $task['userScore'],
            'selectType'=> $task['selectType'],
            'createTime'=> $task['addTime'],
            'finishTime'=> $task['serviceTime'],
            'endTime'   => $task['endTime'],
            'requestnum'=> $task['requestNum'],
            'requested' => !empty($req) ? 1 : 0,                                //是否已抢单
            'selected' => $this->stylistId == $task['hairstylistId'] ? 1 : 0,   //是否被选中
        );
        
        # user info
        /*
        $usertasks = M('bounty_task')->where(array('userId' => $task['userId']))->select();
         */
        $usertasks = D('Bounty')->getBountyTaskByUserId($task['userId'], 2, 4);
        if($usertasks === false)
            $this->error(10);
        $userScores = array();
        foreach($usertasks as $usertask)
            $usertask['userScore']>0 && $userScores[] = $usertask['userScore'];
        
        /*
        $user = M('user')->where(array('user_id' => $task['userId']))->find();
         */
        $user = D('User')->getUserById($task['userId']);
        if(empty($user))
            $this->error(10);
        
        $other = array(
            'img' => $user['img'],
            'mobile' => $user['mobilephone'],
            'name' => $task['name'],
            'tasknum' => count($usertasks),
            'credit' => number_format(array_sum($userScores)/count($userScores), 2),
        );
        
        # comment
        /*
        $comment = $task['isComment'] == 2 ? M("bounty_comment")->where(array('btSn' => $sn, 'type' => 2))->find() : array();
         */
        $comment = $task['isComment'] == 2 ? D('BountyComment')->getCommentBySn($sn, 2) : array();
        if($comment === false)
            $this->error(10);
        
        if(!empty($comment))
            $main['comment'] = array(
                'name'   => $task['name'],
                'userimg'=> $user['img'],
                'userLevel'=> D('UserLevel')->getLevelByUid($user['user_id']),
                'content'=> $comment['content'],
                'addTime'=> $comment['addTime'],
                'imgs'   => json_decode($comment['imgSrc'], true),
            );
        else
            $main['comment'] = null;            //评论为空则字段为null
        
        $this->success(array('main' => $main, 'other' => $other));
    }
    
    /**
     * 已抢任务
     */
    public function requests() 
    {
        
        $this->param['status'] = 1;
        $this->bountyhall();
    }
    
    /**
     * 赏金列表
     */
    public function bountyhall()
    {
        //无权接单
        if($this->stylist['grade'] == 0)
            $this->error(3012);
        
        $lastLoadFirstPageTime = intval($this->param['time']);
        $page = intval($this->param['p']);
        $status = intval($this->param['status']);
        $page = $page>=1 ? ($page-1) : 0;
        $size = intval($this->param['size']);
        $size = $size>0 ? $size : 5;
        
        $time = $page == 0 ? time() : $lastLoadFirstPageTime;
        //任务列表
        /*
        $pushs = M('bounty_push')->where(array('stylistId' => $this->stylistId, "reqStatus" => $status, 'addTime' => array('elt', $time)))
                    ->order('addTime desc')->limit($page*$size, $size)->select();
         */
        $pushs = D('BountyPush')->getStylistPushTask($this->stylistId, $status, $time, $page, $size);
        //新增任务数(不是加载第一页的时候前端用不到，可以不查询)
        if($page != 0)
            $newtasknum = 0;
        else
            /*
            $newtasknum = (int)M('bounty_push')->field('count(1) as total')->where(array('stylistId' => $this->stylistId, "reqStatus" => $status, 'addTime' => array('gt', $lastLoadFirstPageTime)))->find()['total'];
             */
            $newtasknum = D('BountyPush')->getStylistNewPushTaskNum($this->stylistId, $status, $time);
        
        $newtasknum = intval($newtasknum);
//        echo M('bounty_push')->getlastsql();
        if($pushs === false)
            $this->error(2);
        if(empty($pushs))
            $this->success(array('main' => [], 'other' => array('serverTime' => time(), 'newtasknum' => $newtasknum)));
        
        $main = array();
        foreach($pushs as $push)
        {
            /*
            $task = M('bounty_task')->where(array('btSn' => $push['btSn']))->find();
             */
            $task = D('Bounty')->getBountyTaskBybtSn($push['btSn']);
            # already pick someone; ignore
//            if($task['btStatus'] != 1 && $task['hairstylistId'] != $this->stylistId)
//                continue;
            
            if($task['taskType'] == 2)
            {
                $detail = json_decode($task['detail'], true);
                $needsarr = array();
                foreach($detail as $each)
                {
                    if(empty($each['needsStr'])) continue;
                    
                    $needsarr = array_merge($needsarr, explode(',', $each['needsStr']));
                }
                $needs = implode(',', array_unique($needsarr));
                $madeto = '闺蜜X'.count($detail);
                $reason = null;
            }
            else
            {
                $needs = $task['needsStr'];
                $madeto = $this->madetostr($task['madeTo']);
                $reason = $task['reason'];
            }
            $needsStr = str_replace(',', '+', $needs);
            $main[] = array(
                "needs" => $needs,
                "money" => $task['money'],
                "sn" => $task['btSn'],   
                "status" => $task['btStatus'],
                "satisfy" => $task['satisfyType'],
                "type" => $task['taskType'],
                "score" => $task['userScore'],
                "addTime" =>$task['addTime'],
                "madeto" => $madeto,
                "reason" => $reason,
                "remark" => empty(  $task['remark'] ) ? '' : $task['remark'],
                "needsStr" => $needsStr,
            );
            
        }
        $this->success(array('main' => $main, 'other' => array('serverTime' => time(), 'newtasknum' => $newtasknum)));
    }

    /**
     * 气泡信息
     */
    public function bubbleinfo()
    {
        $ongoing = intval($this->param['ongoing']);                             //用户上一次查看进行中任务的时间
        $paid = intval($this->param['paid']);                                   //已打赏
        $canceled = intval($this->param['canceled']);                           //取消
        $times = array(2 => $ongoing, 3 => $ongoing, 4 => $paid, 9 => $canceled);
        /*
        $taskObj = M('bounty_task');
         */
        $timefield = array(2 => 'selectTime', 3 => 'serviceTime', 4 => 'endTime', 9 => 'endTime');
        $mainfield = array(2 => 'ongoing', 3 => 'ongoing', 4 => 'paid', 9 => 'canceled');
        foreach($times as $status => $time)
        {
            /*
            $result = $taskObj->field('count(1) as total')->where("hairstylistId={$this->stylistId} and btStatus={$status} and {$timefield[$status]}>{$time}")->count();
             */
            $result = D('Bounty')->getStylistNewBountyTaskNum($this->stylistId, $status, $time);
            $main[$mainfield[$status]]+= $result;
        }
        $other = array("serverTime" => $time);
        $this->success(array('main' => $main, 'other' => $other));
    }
    
    private function madetostr($madeto)
    {
        $madetoarr = array(1 => '自己',2 => '闺蜜',3 => '女友',4 => '男友',5 => '妈妈',6 => '小宝贝',7 => '其他',);
        return isset($madetoarr[$madeto]) ? $madetoarr[$madeto] : '';
    }
    
}
