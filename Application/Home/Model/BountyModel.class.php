<?php

namespace Home\Model;
use Think\Model;

class BountyModel extends BaseModel {

    protected $tableName = 'bounty_task';

    private $_satisfy = 1;  //满意
    private $_unsatisfy = 2;  //不满意
    private $_paid = 2;  //已经支付
    //给谁做
    public $madetoArr=array(1=>'自己',2=>'闺蜜',3=>'女友',4=>'男友',5=>'妈妈',6=>'小宝贝',20=>'其他');
    //为啥做
    public $reasonArr=array(1=>'就是想换个发型',2=>'约会造型',3=>'生日造型',4=>'纪念日造型',5=>'婚礼造型',6=>'成人礼造型');
	// 已经评价了的状态
	const overComment = 2;
	
    /**
     * 获取赏金单信息数量
     * @deprecated since version thrift
     * @param $where
     * @return mixed
     */
    public function _getCount($where) {
        
        /*
        return M('bounty_task')->where($where)->count();
         */
    }

    /**
     * 获取赏金单编号
     * @param $source
     * @return string
     */
    private function _getBountySn( ) {
        $pre = substr(time(),2);

        $end = '';
        for($i=0;$i<3;$i++) {
            $end .= rand(0,9);
        }

        $code = $pre.'11'.$end;
        /*
        $where = array('btSn'=>$code);
        $count = $this->_getCount($where);
        if($count) {
            return $this->_getBountySn();
        } else {
            return $code;
        }
         */
        $bounty = $this->getBountyTaskBybtSn($code);
        if(!empty($bounty))
            return $this->_getBountySn();
        else
            return $code;
    }

    /**
     * 添加赏金单数据
     * @param int $userId
     * @param int $salonId
     * @param int $money
     * @param string $needsStr
     * @param string $name
     * @param string $madeTo
     * @param string $reason
     * @param string $district
     * @param string $zone
     * @param int $selectType
     * @param string $remark
     * zhigui.zhang@choumei.cn
     * 2015-05-15
     */
    public function addBounty( $data )
    {
        /*
        $bountyTask = M('bounty_task');
         */
        $data['btSn'] = $this->_getBountySn();
        /*
        $result = $bountyTask->add($data); 
         */
        $userId=$data['userId'];
        $money=$data['money'];
        $needsStr=$data['needsStr'];
        $name=$data['name'];
        $madeTo=$data['madeTo'];
        $reason=$data['reason'];
        $district=$data['district'];
        $zone=$data['zone'];
        $selectType=$data['selectType'];
        $remark=$data['remark'];
        $addTime=$data['addTime'];
        $thrift = D('ThriftHelper');
        
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddBountyTaskParam();
        $param->addTime = $addTime;
        $param->btSn = $data['btSn'];
        $param->district = $district;
        $param->madeTo = $madeTo ?: 0;
        $param->money = $money;
        $param->name = $name ?: '';
        $param->needsStr = $needsStr;
        $param->reason = $reason ?: 0;
        $param->remark = $remark ?: '';
        $param->selectType = $selectType;
        $param->userId = $userId;
        $param->zone = $zone;
        $param->detail = $data['detail'] ?: '';
        $param->taskType = $data['taskType'] ? $data['taskType'] : 1;
        $param->salonId = $data['salonId'] ?: 0;
        $param->stylistId = $data['hairstylistId'] ?: 0;
        
        $result = $thrift->request('trade-center', 'addBountyTask', array($param));
        /*
        $result = $thrift->request('trade-center', 'addBountyTask', array($userId,$money,$needsStr,$name,$madeTo,$reason,$district,$zone,$selectType,$remark,$addTime, $data['btSn']));
         */
        if($result !== false)
        {
            return $data['btSn'];
        }
        return false;
    }

    public function getRequestNum( $bountySn )
    {
        /*
        $bountyRequest = M('bounty_request');
        $num = $bountyRequest->where("btSn={$bountySn}")->count();
         */
        $bountyRequest = D('BountyRequest');
        $num = $bountyRequest->getBountyRequestCountBySn($bountySn);
        return $num;
    }

    /**
     * 
     * 不满意时添加评论表
     * @param $data
     * zhigui.zhang@choumei.cn
     * 2015-05-16
     */
    public function addBountyComment( $data )
    {
        /*
        $bountyComment = M('bounty_comment');
        $res = $bountyComment->add($data);
         */
        $res = D('BountyComment')->addComment($data);
        if(!$res){
            $this->eroor = 2505;
            return false;
        }
        /*
        $bountyTask = M('bounty_task');
        $updateData = array(
            'btStatus' => 4,
            'satisfyType' => $this->_unsatisfy,
            'refundStatus' => 5,  //同时更改状态为申请退款
            'endTime' => time(),  //悬赏完成时间
        );
        $result = $bountyTask->where("btSn={$data['btSn']}")->save($updateData);
         */
        $btSn=$data['btSn'];
        $satisfyType=$this->_unsatisfy;
        $refundStatus=5;//同时更改状态为申请退款
        $endTime=time(); //悬赏完成时间
        $btStatus=4;
        /*
        $result=D(Bounty)->updateBountyTaskBybtSnAndRef($btSn,$satisfyType,$refundStatus,$endTime,$btStatus);
         */
        $result = $this->updateBountyTaskOnFinish($btSn, $satisfyType);
        if($result === false)
        {
            $this->error = 2504;
            return false;            
        }
        return true;
    }

    /**
     * 用户选择满意时更新任务表
     * @param $bountySn
     * zhigui.zhang@choumei.cn
     * 2015-05-16
     */
    public function modiBountyTask( $bountySn )
    {
        /*
        $bountyTask = M('bounty_task');
        $bountyTask->startTrans();
         */
        /**
        $result = $bountyTask->where("btSn={$bountySn}")->setField(array('satisfyType' => $this->_satisfy,'isPay' => $this->_paid, 'btStatus'=>4, 'endTime' => time()));
         */
        $thrift = D('ThriftHelper');
        /*
        $result = $thrift->request('trade-center', 'updateBountyTaskBybtSnAndIspay', array($bountySn, $this->_satisfy,$this->_paid, 4, time()));
         */
        $result = $this->updateBountyTaskOnFinish($bountySn, $this->_satisfy);
        if($result === false) //
        {
            /*
            $bountyTask->rollback();
             */
            $this->error = 2503;
            return false;
        }
        //根据单号找到用户id和金额
        /*
         $res1 = M('bounty_task')->field('userId,money')->where("btSn={$bountySn}")->find(); 
         */
        $res1 = $thrift->request('trade-center', 'getBountyTaskBybtSn', array($bountySn));
        if(!$res1){
            /*
            $bountyTask->rollback();
             */
            $this->error = 2521;
            return false;
        }
        //更改用户成长值
        /*
        $res2 = M('user')->where('user_id = %d',$res1['userId'])->setInc('growth', $res1['money']);
         */
        $res2 = D('User')->updateUserGrowth($res1['userId'],$res1['money']);
        if($res2 === false){
//            $bountyTask->rollback();
            $this->error = 1004;
            return false;
        }
//        $bountyTask->commit();
        return true;
    }
	
	
	
	/***
	* 获取验证条件是否在表中存在
	* @param $where 条件
	* @lufangrui
	* @2015-05-16
	***/
        /**
         * 
         * @param type $where
         * @return type
         * @deprecated since version thrift_150709
         */
	public function tastExists( $where ){
		$bountyTaskModel = D('bounty_task');
		$count = $bountyTaskModel->where( $where )->count();
		return $count;
	}
	/***
	* 获取用户赏金任务完成未评价和评价列表信息
	* @param $userId 用户id
	* @param $commentStatus 评论状态
	* @param $page 第几页
	* @param $pageSize 每页条数
	* @lufangrui
	* @2015-05-16
	***/
	public function taskList( $userId,$commentStatus,$page,$pageSize ){
		$fields1 = array('name','money','satisfyType','bounty_task.hairstylistId','salonId','bounty_task.btSn','taskType');
		$where1 = ' bounty_task.userId=%s and btStatus =4 and isComment=%d and bounty_comment.type=2 ';
		$condition = array($userId,$commentStatus);
		if($commentStatus == self::overComment){
            /*
			$Model = M('bounty_task');
			$order = ' bounty_comment.addTime desc ';
            $returns = $Model->alias('bounty_task')->field($fields1)->join($Model->tablePrefix.'bounty_comment bounty_comment ON bounty_task.btSn = bounty_comment.btSn','left')->where($where1,$condition)->order( $order )->page($page,$pageSize)->select();
             */
            //获取已评价任务列表
            $thrift = D('ThriftHelper');
            $tasks = $thrift->request('trade-center', 'getCommentedTaskList', array($userId, $page, $pageSize));
            $returns = array();
            foreach($tasks as $task)
            {
                $returns[] = array(
                    'name' => $task['name'],
                    'money' => $task['money'],
                    'satisfyType' => $task['satisfyType'],
                    'hairstylistId' => $task['hairstylistId'],
                    'salonId' => $task['salonId'],
                    'btSn' => $task['btSn'],
                    'taskType' => $task['taskType'],
                );
            }
            
            return $returns;
		}
        /*
		$bountyTaskModel = D('bounty_task');
		$fields2 = array('name','money','satisfyType','hairstylistId','salonId','btSn','taskType');
		$order = ' serviceTime desc ';
		$where2 = ' userId=%s and btStatus =4 and isComment=%d ';
		$returns = $bountyTaskModel->field( $fields2 )->where($where2,$condition)->order( $order )->page($page,$pageSize)->select();
         */
		$thrift = D('ThriftHelper');
        $endTasks = $thrift->request('trade-center', 'getTaskList', array($userId, $commentStatus, 4, $page, $pageSize));
        $returns = array();
        foreach($endTasks as $task)
        {
            $returns[] = array(
                'name' => $task['name'],
                'money' => $task['money'],
                'satisfyType' => $task['satisfyType'],
                'hairstylistId' => $task['hairstylistId'],
                'salonId' => $task['salonId'],
                'btSn' => $task['btSn'],
                'taskType' => $task['taskType'],
            );
        }
        return $returns;
    }
	/***
	* 获取用户赏金任务完成未评价和评价统计信息
	* @param $userId 用户id
	* @lufangrui
	* @2015-05-16
	***/
	public function taskCommentCount( $userId ){
            /*
            $bountyTaskModel = D('bounty_task');
            $field1 = array('count(btId) as all');
            $where1 = ' userId=%s and btStatus = 4 ';
            $condition = array( $userId );
            // 求到总的赏金任务记录条数
            $all = $bountyTaskModel->field( $field1 )->where( $where1,$condition )->count();
            */
            // 求到总的赏金任务记录条数
            $thrift = D('ThriftHelper');
            $all = $thrift->request('trade-center', 'getBtStatusCount', array($userId,4));
            if( empty($all) )
                return null;
            // 求到赏金发布未评价的记录条数
            /*
            $field2 = array('count(btId) as noAppraisalNum');
            $where2 = ' userId = %s  and btStatus = 4  and isComment = 1 ';
            $temp = $bountyTaskModel->where( $where2,$condition )->count();
            */
            $temp=$thrift->request('trade-center', 'getNotCommentCount', array($userId));
            $noAppraisalNum = empty( $temp ) ? 0 : $temp;
            $appraisalNum = $all - $noAppraisalNum;
            return array(
                'noAppraisalNum' => $noAppraisalNum,
                'appraisalNum' => $appraisalNum
            );
    }

    /***
	* 修改赏金单号为已评价
	* @param $btSn 赏金单号
	* @param $userId 用户id
	* @lufangrui
	* @2015-05-16
	***/
	public function modifyComment( $btSn , $userId ){
            /*
		$bountyTaskModel = D('bounty_task');
		$where = ' btSn = %s and userId = %s ';
		$condition = array( $btSn , $userId );
		$data['isComment'] = 2;
		$bool = $bountyTaskModel->where( $where , $condition )->save( $data );
		return $bool;
             */
            $thrift = D('ThriftHelper');
            $return = $thrift->request('trade-center', 'updateIsComment', array($btSn , $userId ));
            return $return;
	}
	
	/***
	* 获取基本信息
	* @param $btSn 赏金单号
	* @param $userId 用户id
	* @lufangrui
	* @2015-05-16
	***/
	public function getBountyTask( $btSn , $userId ){
            /**
             $bountyTaskModel = D("bounty_task");
		$fields = array(
			'money','salonId','hairstylistId',
			'needsStr','name','madeTo',
			'reason','district','zone',
			'remark','selectType','requestNum',
			'satisfyType','btStatus','isComment',
			'addTime','selectTime','serviceTime','endTime',
			'taskType','detail'
		);
		$where = 'btSn=%s and userId = %s';
		$condition = array( $btSn,$userId );
		$return = $bountyTaskModel->field( $fields )->where( $where,$condition )->find();
		return $return;
             */
		$thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyTask', array($btSn , $userId));
        return $return;
	}
    
    /**
     * 根据抢单金额获取通知造型师信息(还要满足特定区域商圈的造型师)
     */
    public function informStylist($money,$district=0,$zone=0){
        //获取金额对应的店铺等级
        $salonCats = D('Category')->getSalonCatsByPrice($money);
        //获取金额对应的发型师等级，比如600 对应2,3,4
        $stylistCats = D('Category')->getStylistCatsByPrice($money);
//        print_r($salonCats);print_r($stylistCats);exit;
        /*
        if(empty($district) && empty($zone)){
            $where = array(
                'bountyType' => array('in', $salonCats),  
                'grade' => array('in', $stylistCats),
                'stylistStatus' => 1, //正常
                'salestatus' => 1  //正常
                );
        }elseif(!empty($district) && empty($zone)){
            $where = array(
                'bountyType' => array('in', $salonCats),
                'grade' => array('in', $stylistCats),
                'stylistStatus' => 1, //正常
                'salestatus' => 1,  //正常
                'district' => $district
                );
        }else{
            $where = array(
                'bountyType' => array('in', $salonCats),
                'grade' => array('in' , $stylistCats), 
                'district' => $district,
                'zone' => $zone ,
                'stylistStatus' => 1,
                'salestatus' => 1  //正常
                );
        }        
        $stylistInfo = D('StylistAndSalonView')->field('stylistId,osType')->where($where)->select();
         */
        $stylistInfo = D('StylistAndSalonView')-> getStylistByGrade($salonCats,$stylistCats,$district,$zone);
//        echo D()->getLastSql();exit;
        return $stylistInfo;
    }


    /**
     * 获取排行榜的缓存  排行榜缓存每天更新一次
     * @param $sKey
     * @return mixed|null
     */
    private function getRanklistCache($sKey){
        $cacheData=S($sKey); //取出缓存
        //print_r($levelArr);
        if(!$cacheData){
            return false;
        }
        //如果数据是头一天的 则表示该数据无效
        $today=date('Ymd');
        if($today>$cacheData['date']){
            return false;
        }
        return $cacheData['info'];
    }


    /**
     * 设置排行榜的缓存
     * @param $sKey
     * @param $data
     */
    private function setRanklistCache($sKey,$data){
//        $sTime=86400;      //时间 一天
        
         if (ENVIRONMENT!='prod') {   //如果非正式环境  缓存1分钟  便于测试
            $sTime = 60;      //时间 1分钟    
        } else {
            $sTime = 600;      //时间 10分钟    
        }

        $cacheData['date']=date('Ymd');
        $cacheData['info']=$data;

        S($sKey,$cacheData,array('type'=>'file','expire'=>$sTime));//存入缓存
    }


    /**
     * 获取用户赏金总额排行榜
     * @return bool|mixed|null
     */
    public function getTotalRanklist($district) {
        /*
        $sKey = 'totalRanklist' . $district;  //缓存key
        $list = $this->getRanklistCache($sKey);
        if (!$list) {
            $endDate = strtotime(date('Y-m-d'));

            $sql = 'select btId,userId,name as nameStr,sum(money) as total from __PREFIX__bounty_task where btStatus=4 and satisfyType=1 and addTime<' . $endDate . '';
            if (!empty($district)) {
                $sql.=" AND district={$district} ";
            }
            $sql.= ' group by userId order by total desc limit 10 ';
            $list = $this->query($sql);
            //echo $this->_sql();
            if (!$list) {
                return false;
            }
            foreach($list as &$listV){
                $userInfo=D('user')->field('nickname')->find($listV['userId']);
                $listV['nameStr']=$userInfo['nickname'];

                $listV['money']=$listV['total'];
                unset($listV['total']);
                unset($listV['userId']);
            }
            $this->setRanklistCache($sKey,$list);
        }

        return $list;
         */
        
        $sKey = 'totalRanklist' . $district;  //缓存key
        $list = $this->getRanklistCache($sKey);
        if ($list)  
            return $list;
        /*
        $endDate = strtotime(date('Y-m-d'));
         */

        $thrift = D('ThriftHelper');
        $data = $thrift->request('trade-center', 'getTotalRanklist', array(4,1,time()));
        if(!$data) 
            return false;
        foreach($data as $listV){
            $user=D('User')->getUserById($listV['userId']);
            $list[] = array(
                'nameStr' => $user['nickname'],
                'money' => $listV['totalMoney'],
                );
        }
        
        
        $this->setRanklistCache($sKey,$list);
        return $list;
    }


    /**
     * 获取用户单次赏金金额排行榜
     * @return bool|mixed|null
     */
    public function getSingleRanklist($district){
        $sKey='singleRanklist' . $district;  //缓存key

        $list = $this->getRanklistCache($sKey);
        if(!$list){
            /*
            $endDate=strtotime(date('Y-m-d'));
             */

            $where['btStatus']=4;
            $where['satisfyType']=1;
            $where['addTime'] = array('LT', $endDate);
            if (!empty($district)) {
                $where['district'] = $district;
            }
            /*
            $list=$this->field('btId,userId,name as nameStr,money')->where($where)->order('money desc')->limit(10)->select();
            */
            $thrift = D('ThriftHelper');
            $list = $thrift->request('trade-center', 'getSingleRanklist', array(4,1,time(), $district));
            //echo $this->_sql();
            if(!$list){
                return false;
            }
            foreach($list as &$listV){
                /*
                $userInfo=D('user')->field('nickname')->find($listV['userId']);
                 */
                $userInfo=D('User')->getUserById($listV['userId']);
                $listV['nameStr']=$userInfo['nickname'];
                unset($listV['userId']);
            }
            $this->setRanklistCache($sKey,$list);
        }

        return $list;
    }
    
    
    
    
    /*
     *========================liyu============================ 
     */
    
     /*
     * 根据id获取任务信息
     */

    /*
    public function getTaskById($id, $field = "*", $where = "") {
        if (empty($where)) {
            $data = $this->field($field)->find($id);
        } else {
            $data = $this->field($field)->where($where)->find();
        }
        return $data ? $data : FALSE;
    }
     */
    public function getTaskById($btId,$userId,$btStatus) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getTaskById', array($btId,$userId,$btStatus));
        return $return;

    }
    /*
     * 获取列表
     */

    /*
    public function getTask($field, $where, $page, $pageSize, $order, $totalNum) {
        if (!$totalNum) {
            $totalNum = $this->where($where)->count();
        }
        $data = $this->field($field)->where($where)->page($page, $pageSize)->order($order)->select();
        return array("data" => $data, "totalNum" => $totalNum);
    }
     */
    //**waiting for implement
    public function getTask($userId, $btStatus, $page, $pageSize, $totalNum) {
        /*
        $thrift = D('ThriftHelper');
        $data = $thrift->request('trade-center', 'getTask', array($userId, $btStatus, $page, $pageSize, $totalNum));
         */
        $data = $this->getUserBountyTasks($userId, $btStatus, $page, $pageSize, $totalNum);
        foreach ($data["bTList"] as $key=>$bt) {
            $data["bTList"][$key]["bountySn"]=$bt["btSn"];
        }
         if (!$totalNum) {
            $totalNum = $data["totalNum"];
        }
        return array("data" => $data["bTList"], "totalNum" => $totalNum);
    }
    
    public function getUserBountyTasks($userId, $btStatus, $page, $pageSize, $totalNum = 0)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'getTask', array($userId, $btStatus, $page, $pageSize, $totalNum));
    }
    
    /**
     * 将推送的消息存入数据库push表
     * @author huliang
     * @deprecated since version thrift_150709
     */
    public function addToPush($data){
        /*
        $bountyPush = M('bounty_push');
        $res = $bountyPush->addAll($data);
        if(!$res){
            $this->eroor = 2601;
            return false;
        }
        return true;
         */
    }
    
    /**
     * 为我服务过的造型师
     */
    public function myServiceStylist($field, $where,$order,$page, $pageSize, $totalNum){
        if(!$totalNum){
            $totalNum = $this->where($where)->count('distinct hairstylistId');
        }
        $data = $this->field($field)->where($where)->page($page, $pageSize)->group('hairstylistId')->order($order)->select();
        return array("data" => $data, "totalNum" => $totalNum);
    }
    
    //判断造型师是否符合条件，比如店铺休假，造型师离职了等等
    public function canChooseStylist($stylistId){
        /*
        $stylistInfo = D('StylistAndSalonView')->field('stylistStatus,salestatus')->where(array('stylistId' => $stylistId))->find();
        if($stylistInfo['salestatus'] != 1){
            $this->eroor = 2524;
            return false;
        }
        if($stylistInfo['stylistStatus'] != 1){
            $this->eroor = 2525;
            return false;
        }
        return true;
         */
        $stylistInfo = D('HairStylist')->getHairstylist($stylistId);
        if($stylistInfo['status'] != 1)
        {
            $this->eroor = 2525;
            return false;
        }
        $salon = D('Salon')->getSalonById($stylistInfo['salonId']);
        if($salon['status'] != 1) //thrift没吐salestatus，先用status代替
        {
            $this->eroor = 2524;
            return false;
        }
        return true;
    }
    
	//**wait for implement
    //得到不同任务状态的任务数目
    public function getBtStatusCount($userId,$btStatus){
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBtStatusCount', array($userId,$btStatus));
        return $return;           
              
    }
    //得到没评论的任务数目,btStatus=4,iscomment=1
    public function getNotCommentCount($userId){
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getNotCommentCount', array($userId));
        return $return;                   
    }
    /**
     * 通过userId拿到BountyTask信息
     */
    public function getBountyTaskByUserId($userId,$ispay,$btStatus) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyTaskByUserId', array($userId,$ispay,$btStatus));
        return $return;          
    }
    
    /**
     * 通过btSn拿到BountyTask信息
     */
    public function  getBountyTaskBybtSn($btSn) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyTaskBybtSn', array($btSn));
        return $return;         
    }
    
     /**
     * 通过hairstylistId和满意类型拿到该造型师被打赏或者被取消打赏的次数
     */
    public function getHairstylistBountyCount($hairstylistId,$satisfy) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getHairstylistBountyCount', array($hairstylistId,$satisfy));
        return $return;           
    }
    
    /**
     * 通过赏金单号更新赏金任务信息
     */
    public function  updateBountyTaskBybtSn($btSn,$salonId,$stylistId,$btStatus,$requestNum,$addTime,$selectTime) {
        $thrift = D('ThriftHelper');
        /*
        $return = $thrift->request('trade-center', 'updateBountyTaskBybtSn', array($btSn,$salonId,$stylistId,$btStatus,$requestNum,$addTime,$selectTime));
         */
        $param = new \cn\choumei\thriftserver\service\stub\gen\UpdateBountyTaskBybtSnAndSalonIdParam();
        $param->addTime = $addTime;
        $param->btSn = $btSn;
        $param->btStatus = $btStatus;
        $param->requestNum = $requestNum;
        $param->salonId = $salonId;
        $param->selectTime = $selectTime;
        $param->stylistId = $stylistId;
        $return = $thrift->request('trade-center', 'updateBountyTaskBybtSnAndSalonId', array($param));
        return $return;
        
    }
    
    /**
     * 发布消息列表
     */
    public function getBountyTaskList($startTime,$endTime,$isPay) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyTaskList', array($startTime,$endTime,$isPay));
        return $return;
        
    }
    
     /**
     * 根据status获取任务信息
     */
    public function getBountyTaskByStatus($btStatus,$userId) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getBountyTaskByStatus', array($btStatus , $userId));
        return $return;
    }
    
    public function getStylistBountyTask($stylistId, $status, $time, $page, $size)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getStylistBountyTask', array($stylistId, $status, $time, $page, $size));
        return $return;
    }
    
    public function getStylistNewBountyTaskNum($stylistId, $status, $time)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getStylistNewBountyTaskNum', array($stylistId, $status, $time));
        return $return;
    }
    
    public function updateBountyTaskBybtSnAndSalonId($btSn, $salonId, $stylistId, $btStatus, $requestNum, $addTime, $selectTime)
    {
        $thrift = D('ThriftHelper');
        $param = new \cn\choumei\thriftserver\service\stub\gen\UpdateBountyTaskBybtSnAndSalonIdParam();
        $param->addTime = $addTime;
        $param->btSn = $btSn;
        $param->btStatus = $btStatus;
        $param->requestNum = $requestNum;
        $param->salonId = $salonId;
        $param->selectTime = $selectTime;
        $param->stylistId = $stylistId;
        $return = $thrift->request('trade-center', 'updateBountyTaskBybtSnAndSalonId', array($param));
        /*
        $return = $thrift->request('trade-center', 'updateBountyTaskBybtSnAndSalonId', array($btSn, $salonId, $stylistId, $btStatus, $requestNum, $addTime, $selectTime));
         */
        return $return;
    }
    
    public function stylistUpdateTask($btSn, $status, $requestnum, $score)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'stylistUpdateTask', array($btSn, $status, $requestnum, $score));
        return $return;
    }
    
    public function updateIsComment($btSn, $userId) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updateIsComment', array($btSn, $userId));
        return $return;
    }
    
    
    public function updateStylistSelectedBySn($data) {
        $btSn=$data['btSn'];
        $salonId=$data['salonId'];
        $hairstylistId=$data['hairstylistId']; 
        $btStatus=$data['btStatus'];
        $selectTime=$data['selectTime'];
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updateStylistSelectedBySn', array($btSn,$salonId,$hairstylistId,$btStatus,$selectTime));
        return $return;
    }
    
    /**
   *获取未服务的赏金任务
   */
    public function getNotServeBountyTask($bountySn,$userId)
    {
        /*
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getNotServeBountyTask', array($bountySn,$userId));
        return $return;
         */
        $bounty = $this->getBountyTaskBybtSn($bountySn);
        if(empty($bounty) || $bounty['userId'] != $userId)
            return null;
        return $bounty;
    }
    
    /**
   *取消赏金任务
   */
    public function cancelTask($btSn, $userId)
    {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'cancelTask', array($btSn, $userId));
        return $return;
    }
    
    /**
   *查询待抢单赏金任务的数目
   */
    public function getNotSelectBtCount($userId)
    {
        /*
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getNotSelectBtCount', array($userId));
        return $return;
         */
    }
    
    public function updateBountyTaskBybtSnAndRef($btSn,$satisfyType,$refundStatus,$endTime,$btStatus) {
        /*
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updateBountyTaskBybtSnAndRef', array($btSn,$satisfyType,$refundStatus,$endTime,$btStatus));
        return $return;
         */
    }
    
    public function updateBountyTaskOnFinish($btSn,$satisfyType)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('trade-center', 'updateBountyTaskOnFinish', array($btSn,$satisfyType));
    }
}