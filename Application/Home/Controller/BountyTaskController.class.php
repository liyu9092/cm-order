<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * auth yu.li
 */

namespace Home\Controller;
use Think\Log;

class BountyTaskController extends OrderOutController {

    /**
     * 悬赏换发型/任务首页
     * V5.3.1 更改的接口
     * @author carson
     */
    public function index() {
        $BountyObj = D('Bounty');
        $bountyInfo = array();
        if (!empty($this->userId)) {
            $bWhere['userId'] = $this->userId;
            $bWhere['isPay'] = 2;
            $bWhere['btStatus'] = 1;
            /*
             $bountyInfo = $BountyObj->getInfo($bWhere, 'btSn');
             */
            $bountyInfos=$BountyObj->getBountyTaskByUserId($this->userId,2,1);
            $bountyInfo = array_shift($bountyInfos);
        }
        //$bWhere['selectType']=1;


        $data['havetask'] = 1;
        $countW['status'] = 1;
        /*
         $hairstylistNum = M('hairstylist')->where($countW)->count();
         */
        $hairstylistNum = D('HairStylist')->getHairstylistCount($countW['status']);
        $data['nums'] = $hairstylistNum;
        if (!$bountyInfo) {
            $data['havetask'] = 0;
            $data['bountySn'] = '';
        } else {
            $data['bountySn'] = $bountyInfo['btSn'];
        }

        $this->ret['main'] = $data;
        $this->success();
    }

    /*
     * 我的赏金任务
     */

    public function myBountyTask() {
        /*
        $notServiceWhere = array(
            "btStatus" => 2,
            "userId" => $this->userId,
        );
        $notPayWhere = array(
            "btStatus" => 3,
            "userId" => $this->userId
        );
        $notCommentWhere = array(
            "isComment" => 1,
            "btStatus" => 4,
            "userId" => $this->userId
        );
        $notServiceNum = D("Bounty")->where($notServiceWhere)->count();
        $notPayNum = D("Bounty")->where($notPayWhere)->count();
        $notCommentNum = D("Bounty")->where($notCommentWhere)->count();
         */
        //**Waiting for implement
        $notServiceNum = D("Bounty")->getBtStatusCount($this->userId,2);
        $notPayNum = D("Bounty")->getBtStatusCount($this->userId,3);
        $notCommentNum = D("Bounty")->getNotCommentCount($this->userId);
        
        
        $this->ret = array(
            "main" => array(
                "notServiceNum" => $notServiceNum ? $notServiceNum : 0,
                "notPayNum" => $notPayNum ? $notPayNum : 0,
                "notCommentNum" => $notCommentNum ? $notCommentNum : 0
            )
        );
        $this->success();
    }

    /*
     * 待服务/待打赏 的任务列表
     */

    public function forService() {
        $param = $this->param;
        $page = $param['page'] ? $param['page'] : 1;
        $pageSize = $param['pageSize'] ? $param['pageSize'] : 10;
        $totalNum = $param['totalNum'];
        $type = $param['type'] ? $param['type'] : 2;
        if ($type && !in_array($type, array("2", "3"))) {
            $this->error(1);  //type必须为2或3
        }
        /*
        $field = array('btId', 'taskType', 'bountySn', 'name', 'money', 'stylistId', 'stylistImg', "stylistName", "job", "mobilephone", "salonName", "salonId", "addrLati", "addrLong", "addr");
        $where = array(
            "userId" => $this->userId,
            "btStatus" => $type
        );
        $order = $type == 2 ? " addTime desc " : " serviceTime desc ";
        $data = D("BountyHairstylistSalonView")->field($field)->where($where)->order($order)->select();
        if (!$totalNum) {
            $totalNum = D("BountyHairstylistSalonView")->where($where)->count();
        }
         */
        //**Waiting for implement
        
        /*
        $bountyTasks=D('Bounty')->getBountyTaskByStatus($type,$this->userId);
        $totalNum = count($bountyTasks);
         */
        $bountyTaskInfo = D('Bounty')->getTask($this->userId, $type, $page-1, $pageSize, $totalNum);
        $bountyTasks = $bountyTaskInfo['data'];
        $totalNum = $bountyTaskInfo['totalNum'];
        
        foreach ($bountyTasks as  $bountyTask) {
            $hairStyList=D('HairStylist')->getHairstylist($bountyTask['hairstylistId']);
            $salon=D('Salon')->getSalonById($hairStyList['salonId']);
            $d['btId']=$bountyTask['btId'];
            $d['taskType']=$bountyTask['taskType'];
            $d['bountySn']=$bountyTask['btSn'];
            $d['name']=$bountyTask['name'];
            $d['money']=$bountyTask['money'];

            $d['stylistId']=$hairStyList['stylistId'];
            $d['stylistName']=$hairStyList['stylistName'];
            $d['stylistImg']=$hairStyList['stylistImg'];
            $d['job']=$hairStyList['job'];
            $d['mobilephone']=$hairStyList['mobilephone'];
            
            $d['salonName']=$salon['salonname'];   
            $d['salonId']=$salon['salonid']; 
            $d['addrLati']=$salon['addrlati']; 
            $d['addrLong']=$salon['addrlong']; 
            $d['addr']=$salon['addr'];
            $data[]=$d;
        }
        $this->ret = array(
            "main" => $data ? $data : array(),
            "other" => array(
                "totalNum" => $totalNum ?: 0,
            )
        );
        $this->success();
    }

    /*
     * 取消的任务列表
     */

    public function cancelTaskList() {
        $param = $this->param;
        $page = $param['page'] ? $param['page'] : 1;
        $pageSize = $param['pageSize'] ? $param['pageSize'] : 10;
        $totalNum = $param['totalNum'];
        /*
        $field = array('btId', 'taskType', 'btSn' => 'bountySn', 'name', 'money', 'btSn', 'hairstylistId', "endTime");
        $where = array(
            "userId" => $this->userId,
            "btStatus" => 9
        );
        $order = " endTime desc ";
        $data = D("Bounty")->getTask($field, $where, $page, $pageSize, $order, $totalNum);
        $res = array();
        if (!empty($data["data"])) {
            foreach ($data["data"] as $val) {
                $val["endTime"] = date("Y-m-d H:i:s", $val["endTime"]);
                $res[] = $val;
            }
        }
         */
        $dataInfo = D("Bounty")->getTask($this->userId, 9, $page-1, $pageSize, $totalNum);
        $tasks = array();
        foreach($dataInfo['data'] as $task)
        {
            $tasks[] = array(
                'btId' => $task['btId'],
                'taskType' => $task['taskType'],
                'bountySn' => $task['btSn'],
                'name' => $task['name'],
                'money' => $task['money'], 
                'hairstylistId' => $task['hairstylistId'],
                "endTime" => date('Y-m-d H:i:s', $task['endTime']),
            );
        }
        $res = $tasks;
        $data['totalNum'] = $dataInfo['totalNum'];
        $this->ret = array(
            "main" => $res,
            "other" => array("totalNum" => $data["totalNum"] ?: 0)
        );
        $this->success();
    }

    /*
     * 取消的任务详情
     */

    public function cancelTaskDetail() {
        $param = $this->param;
        $btId = $param['btId'] ? trim($param['btId']) : "";
        if (empty($btId)) {
            $this->error(1);
        }
        $model = D("Bounty");
        /*
        $where = array(
            "userId" => $this->userId,
            "btStatus" => 9,
            "btId" => $btId
        );
        $field = array("name", 'taskType', 'btSn' => 'bountySn', "btId", "money", "btSn", "madeTo", "reason", "needsStr", "district", "zone", "remark", "selectType", "addTime", "endTime", "hairstylistId");
        $data = $model->getTaskById($btId, $field, $where);
         */
        $data = $model->getTaskById($btId,$this->userId,9);
        if (!$data) {
            $this->error(3002);
        }
        $data['madeTo'] = !empty($model->madetoArr[$data['madeTo']]) ? $model->madetoArr[$data['madeTo']] : '';
        $data['reason'] = !empty($model->reasonArr[$data['reason']]) ? $model->reasonArr[$data['reason']] : '';
        $data['selectType'] = $data['selectType'] == 1 ? "自己选" : "臭美待选";
        /*
        $district = $model->getTown($data['district']);
         */
        $district = D('Town')->getTown($data['district']);
        $zone = D("SalonArea")->getAreaName($data["zone"]);
        $data['area'] = $district . " " . $zone;
        $this->ret = array(
            "main" => $data,
        );
        $this->success();
    }

    /*
     * 检测是否有相应造型师
     * for V5.3.1根据区域筛选
     */

    public function getHairstylist() {
        $param = $this->param;
        $money = !empty($param['money']) ? $param['money'] : '';
        $district = !empty($param['district']) ? $param['district'] : '';
        $zone = !empty($param['zone']) ? $param['zone'] : '';
        if (empty($money))
            $this->error(1);
        if (intval($money < 200)) {
            $this->error(2515);
        }
        $informStylist = D("Bounty")->informStylist($money, $district, $zone);
        if (empty($informStylist)) {  //没有相应造型师
            $this->error(2523);
        }
        $this->success();
    }

    /**
     * 发布任务接口
     * By zhigui.zhang@choumei.cn
     * Date 2015-05-15
     */
    public function pubBountyTask() {
        if (empty($this->userId)) {
            $this->error(1);
        }
        $paramData = $this->param;
        $data = array();
        $data['userId'] = $this->userId;
        $data['money'] = intval($paramData['money']);
        $data['needsStr'] = $paramData['needsStr'];
        $data['name'] = $paramData['name'];
        $data['madeTo'] = $paramData['madeTo'];
        $data['reason'] = $paramData['reason'];
        $data['district'] = $paramData['district'];
        $data['zone'] = $paramData['zone'];
        $data['selectType'] = $paramData['selectType'];
        $data['remark'] = isset($paramData['remark']) ? $paramData['remark'] : "";
        $data['addTime'] = time();

        if (!$paramData['money'] || !$paramData['needsStr'] || !$paramData['name'] || !$paramData['madeTo'] || !$paramData['reason'] || !isset($paramData['district']) || !isset($paramData['zone']) || !$paramData['selectType'])
            $this->error(1);
        if (intval($paramData['money']) < 200) {
            $this->error(2515);
        }
        //检测当前区域是否有相应的造型师
        $informStylist = D("Bounty")->informStylist($data['money'], $data['district'], $data['zone']);
        if (empty($informStylist)) {  //没有相应造型师
            $this->error(2523);
        }
        //用户如果发布任务后且支付成功没有选择造型师，则不能重新发布任务了
        $where = array(
            'userId' => $this->userId,
            'isPay' => 2,
            'btStatus' => 1
        );
        /*
        $count = M('bounty_task')->where($where)->count();
        if ($count) {
            $this->error(2514);
        }
         */
        $bountyInfo=D('Bounty')->getBountyTaskByUserId($this->userId,2,1);
        if (!empty($bountyInfo)) 
            $this->error(2514);
        
        
        //判断needsStr是否在可选范围内
        $allNeedsStr = array('洗剪吹', '染发', '烫发', '接发', '护发', '到店商议');
        $needsArr = explode(",", $paramData['needsStr']);
        //求交集
        if ($needsArr != array_intersect($needsArr, $allNeedsStr)) {
            $this->error(2512);
        }
        //如果金额，商圈和区域选出来的造型师不存在，则重新发布
        //根据抢单金额获取符合条件的造型师
        if ($paramData['selectType'] == 2) {
            $stylistInfo = D('Bounty')->informStylist(intval($paramData['money']), $data['district'], $data['zone']);
            if (empty($stylistInfo)) {
                $this->error(2520);
            }
        }
        $bountyObj = D('Bounty');
        $bountyObj->startTrans();
        $btSn = $bountyObj->addBounty($data);
        if ($btSn === false) {
            $bountyObj->rollback();
            $this->error(2508);
        }
        $bountyObj->commit();
        //推荐码
        try {
            D('RecommendCodeOrder')->toRecordItOnOrder($btSn,2);
        } catch (Exception $e) {
            Log::write("赏金单{$btSn}推荐码写入失败:".$e->getMessage());
        }
        $main = array(
            'bountySn' => $btSn,
        );

        $this->ret["main"] = $main;
        $this->success();
    }
    
    
    /**
     * 发布闺蜜团任务
     * for V5.3.1
     * @author huliang
     */
    public function pubFriendsTask(){
        if(empty($this->userId)){
            $this->error(1);
        }
        $paramData = $this->param;
        $data = array();
        $data['userId'] = $this->userId;
        $data['money'] = intval($paramData['money']);
        $data['needsStr']='';
        $data['name'] = $paramData['name'];
        $data['madeTo']='';
        $data['reason']=0;
        $data['district'] = intval($paramData['district']);
        $data['zone'] = intval($paramData['zone']);
        $data['selectType'] = intval($paramData['selectType']);
        $data['detail'] = json_encode($paramData['detail'], JSON_UNESCAPED_UNICODE);
        $data['remark']='';
        $data['addTime'] = time();
        $data['taskType'] = 2;
        
        if(!$paramData['money'] || !$paramData['name'] || !isset($paramData['district']) || !isset($paramData['zone']) || !$paramData['selectType'] || !$paramData['detail'] ) $this->error(1);
        //判断needsStr是否在可选范围内
        $allNeedsStr = array('洗剪吹','染发','烫发','接发','护发','到店商议');
        $detail = $paramData['detail'];
        foreach ($detail as $value) {
            $needsArr = explode(",",$value['needsStr']);
            if ($needsArr != array_intersect($needsArr, $allNeedsStr)) { 
                $this->error(2512);
            }
        }       
        //赏金金额不能小于人数*200
        if(intval($paramData['money']) < 200 * count($detail)){
            $this->error(2515);
        }
        
        //检测当前区域是否有相应的造型师
        $informStylist = D("Bounty")->informStylist($data['money'], $data['district'], $data['zone']);
        if (empty($informStylist)) {  //没有相应造型师
            $this->error(2523);
        }
        //用户如果发布任务后且支付成功没有选择造型师，则不能重新发布任务了
        $where = array(
            'userId' => $this->userId,
            'isPay' => 2,
            'btStatus' => 1
        );
        /*
        $count = M('bounty_task')->where($where)->count();
         */
        $bountyTask = D('Bounty')->getBountyTaskByUserId($this->userId,2,1);
        if(empty($bountyTask))
                    $count = 0;
                else
                    $count = 1;
        if($count){
            $this->error(2514);
        }
        //如果金额，商圈和区域选出来的造型师不存在，则重新发布
        //根据抢单金额获取符合条件的造型师
        //如果臭美代选，则先筛选有没有符合条件的造型师
        if($paramData['selectType'] == 2){
            $oneMoney = floor(intval($paramData['money']) / count($detail));
            $stylistInfo = D('Bounty')->informStylist($oneMoney,$data['district'],$data['zone']);
            if(empty($stylistInfo)){
                $this->error(2520);
            }
        }
        $bountyObj = D('Bounty');
        $bountyObj->startTrans();
        $btSn = $bountyObj->addBounty( $data );
        
        /*
        $userId = $data['userId'];
        $money=$data['money'];
        $needsStr='';
        $name=$data['name'] ;
        $madeTo='';
        $reason=0;
        $district=$data['district'];
        $zone=$data['zone'];
        $selectType= $data['selectType'];
        $remark='';
        $addTime=$data['addTime'];
        $btSn = $bountyObj->addBounty( $userId,$money,$needsStr,$name,$madeTo,$reason,$district,$zone,$selectType,$remark,$addTime);
         */
        if($btSn === false){
            $bountyObj->rollback();
            $this->error(2508);
        }else{
            //向bounty_friends表写记录数据
            //print_r($paramData['detail']);exit;
            foreach ($paramData['detail'] as $value) {
                $friendsData[] = array(
                    'bountySn' => $btSn,
                    'name' => $value['name'],
                    'needsStr' => $value['needsStr'],
                    'reason' => $value['reason'],
                    'remark' => $value['remark'],
                    'addTime' => time()
                );
                $result = D('BountyFriends')->addBountyFriends($btSn,$value['name'],$value['needsStr'],$value['reason'],$value['remark'],time());
            }     
            /*
               $result = M('bounty_friends')->addAll($friendsData);
             */
           
            if($result === false){
                $bountyObj->rollback();
                $this->error(2511);
            }
        }
        $bountyObj->commit();
        //推荐码
        try {
            D('RecommendCodeOrder')->toRecordItOnOrder($btSn,2);
        } catch (Exception $e) {
            Log::write("赏金单{$btSn}推荐码写入失败:".$e->getMessage());
        }
        $main = array(
            'bountySn'  => $btSn,
        );
        $this->ret["main"] = $main;
        $this->success();
    }

    /**
     * 赏金排行 
     * V5.3.1根据区域筛选
     * @author carson
     */
    public function ranklist() {
        $type = $this->param['type'];  //榜单类型
        $district = $this->param['district'];  //区域ID
        $BountyObj = D('Bounty');

        if ($type == 2) {
            //总榜
            $list = $BountyObj->getTotalRanklist($district);
        } else {
            //单榜
            $list = $BountyObj->getSingleRanklist($district);
        }
        $this->ret['main'] = '';
        if ($list) {
            foreach ($list as $listK => &$listV) {
                if ($listK < 3) {
                    $noArr = array('冠军', '亚军', '季军');
                    $noStr = $noArr[$listK];
                } else {
                    $noStr = $listK + 1;
                }
//                $listV['nameStr'] = msubstr($listV['nameStr'], 0, 1) . '***' . msubstr($listV['nameStr'], -1, 1);
                $listV['noStr'] = $noStr;
                $listV['money'] = '￥' . $listV['money'];
            }
            $this->ret['main'] = $list;
        }
        $this->ret['other']['type'] = $type;
        $this->ret['other']['title'] = '截止' . date('Y年m月d日', time() - 86400);
//        $this->ret['other']['title'] = '截止' . date('Y年m月d日', time());
        $this->success();
    }
    
    //为我服务过的造型师列表
    public function serviceStylistList(){
        if(empty($this->userId)){
            $this->error(1);
        }
        $paramData = $this->param;
        $page = empty( $paramData["page"] ) ? 1 : $paramData["page"];
        $pageSize = empty($paramData["pageSize"]) ? 10 : $paramData["pageSize"] ;
        $totalNum = $paramData["totalNum"];
        $gradeValue = array('','高级设计师','资深设计师','设计总监','美发大师');  //1高级设计师 2资深设计师 3设计总监 4美发大师
        $userId = $this->userId;
        
        $where = array(
            'userId' => $userId,
            'isPay' => 2,
            'btStatus' => array('in','3,4'),
        );
        $field = array(
            'salonId',
            'hairstylistId',
            'max(serviceTime) as serviceTime',
        );
        $order = 'serviceTime DESC';
        $resInfo = D('Bounty')->myServiceStylist($field,$where,$order, $page, $pageSize, $totalNum);
        if(empty($resInfo['data'])){
           $this->ret['other']['haveServiceStylist'] = 0; 
           $this->ret['other']['totalNum'] = 0; 
           $this->success();
        }
        //根据造型师id获取造型师信息
        //通过salonId 获取店铺名称
        foreach($resInfo['data'] as $key => $value){
            $stylistInfo[$key]['salonId'] = $value['salonId'];
            $stylistInfo[$key]['salonName'] = D('Salon')->getSalonNameById($value['salonId']); 
            $stylistInfo[$key]['hairstylistId'] = $value['hairstylistId'];
            $stylistRes = D('HairStylist')->getHairstylist($value['hairstylistId']);
            $stylistInfo[$key]['stylistName'] = $stylistRes['stylistName'];
            $stylistInfo[$key]['stylistImg'] = $stylistRes['stylistImg'];
            $stylistInfo[$key]['gradeName'] = $gradeValue[$stylistRes['grade']];          
        }
        //print_r($stylistInfo);
        $this->ret['other']['haveServiceStylist'] = 1; 
        $this->ret['other']['totalNum'] = intval($resInfo['totalNum']); 
        $this->ret['main'] = $stylistInfo;
        $this->success();
    }
    
    
    //v5.4.1版本不需要madeTo 和 reason 字段，这两个字段是默认值0，不影响之前的版本
    /**
     * 发布任务接口   
     */
    public function pubNewBountyTask() {
        if (empty($this->userId)) {
            $this->error(1);
        }
        $paramData = $this->param;
        $data = array();
        $data['userId'] = $this->userId;
        $data['money'] = intval($paramData['money']);
        $data['needsStr'] = $paramData['needsStr'];
        $data['district'] = $paramData['district'];
        $data['zone'] = $paramData['zone'];
        $data['selectType'] = $paramData['selectType'];
        $data['remark'] = isset($paramData['remark']) ? $paramData['remark'] : "";
        $data['addTime'] = time();

        if (!$paramData['money'] || !$paramData['needsStr'] || !isset($paramData['district']) || !isset($paramData['zone']) || !$paramData['selectType'])
            $this->error(1);
        if (intval($paramData['money']) < 200) {
            $this->error(2515);
        }
        //检测当前区域是否有相应的造型师
        //有了造型师就不需要检测
        if(!$paramData['stylistId']){
            $informStylist = D("Bounty")->informStylist($data['money'], $data['district'], $data['zone']);
            if (empty($informStylist)) {  //没有相应造型师
                $this->error(2523);
            }
        }        
        //用户如果发布任务后且支付成功没有选择造型师，则不能重新发布任务了
        /*
        $where = array(
            'userId' => $this->userId,
            'isPay' => 2,
            'btStatus' => 1
        );
        $count = M('bounty_task')->where($where)->count();
         */
        $bountyInfos = D('Bounty')->getBountyTaskByUserId($this->userId, 2, 1);
        $count = count($bountyInfos);
        if ($count) {
            $this->error(2514);
        }
        //判断needsStr是否在可选范围内
        $allNeedsStr = array('洗剪吹','烫发','染发', '到店再说');
        $needsArr = explode(",", $paramData['needsStr']);
        //求交集
        if ($needsArr != array_intersect($needsArr, $allNeedsStr)) {
            $this->error(2512);
        }
        //如果金额，商圈和区域选出来的造型师不存在，则重新发布
        //根据抢单金额获取符合条件的造型师
        if ($paramData['selectType'] == 2) {
            $stylistInfo = D('Bounty')->informStylist(intval($paramData['money']), $data['district'], $data['zone']);
            if (empty($stylistInfo)) {
                $this->error(2520);
            }
        }
        //新增为我服务过的造型师
        if ($paramData['selectType'] == 3) {
            $stylistId = $paramData['stylistId'];
            if (empty($stylistId)) {
                $this->error(1);
            }
            //判断造型师是否符合条件，比如店铺休假，造型师离职了等等
            $styObj = D('Bounty');
            $chooseStylistRes = $styObj->canChooseStylist($stylistId);
            if(!$chooseStylistRes){
                $this->error($styObj->getError());
            }           
            $data['hairstylistId'] = $stylistId;
        }
        $bountyObj = D('Bounty');
        $bountyObj->startTrans();
        $btSn = $bountyObj->addBounty($data);
        if ($btSn === false) {
            $bountyObj->rollback();
            $this->error(2508);
        }
        $bountyObj->commit();
        //推荐码
        try {
            D('RecommendCodeOrder')->toRecordItOnOrder($btSn,2);
        } catch (Exception $e) {
            Log::write("赏金单{$btSn}推荐码写入失败:".$e->getMessage());
        }
        $main = array(
            'bountySn' => $btSn,
        );

        $this->ret["main"] = $main;
        $this->success();
    }

}
