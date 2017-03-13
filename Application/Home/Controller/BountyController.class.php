<?php
/**
 * 赏金任务类
 * @author carson
 */
namespace Home\Controller;
use Think\Log;

class BountyController extends OrderOutController{

    private $_comment_unsatisfy = 1;  //评论类型 1为用户打赏不满意信息
    private $_isSatisfy = 1;   // 用户满意
    private $_unSatisfy = 2;   // 用户不满意
	
	CONST TOKEN_KEY = "CHOUmei";
	
	private $_taskType = array(1,2); // 任务类型: 1单个 2闺蜜团
	//1高级设计师 2资深设计师3设计总监4美发大师
	private $grade = array('','高级设计师','资深设计师','设计总监','美发大师');
	
    /**
     * 悬赏换发型/任务首页
     * @author carson
     */
    public function index(){
        $BountyObj=D('Bounty');

        $bWhere['userId']=$this->userId;
        $bWhere['isPay']=2;
        $bWhere['btStatus']=1;
        //$bWhere['selectType']=1;
        /*
        $bountyInfo=$BountyObj->getInfo($bWhere,'btSn');
        */
        $bountyInfo=$BountyObj->getBountyTaskByUserId($this->userId,2,1);
        
        $data['havetask']=1;
        if(!$bountyInfo){
            $data['havetask']=0;

            /*
            $countW['status']=1;
            $hairstylistNum=M('hairstylist')->where($countW)->count();
             */
            $hairstylistNum=D('HairStylist')->getHairstylistCount(1);
            $data['nums']=$hairstylistNum;
        }else{
            $data['bountySn']=$bountyInfo['btSn'];
        }
		
		$this->ret['main']=$data;
        $this->success();
    }


    /**
     * 进行中任务
     * @author carson
     */
    public function waitSelect(){
        $BountyObj=D('Bounty');

        /*
        $bWhere['userId']=$this->userId;
        $bWhere['isPay']=2;
        $bWhere['btStatus']=1;
        //$bWhere['selectType']=1;

        $fields=array(
            'name',
            'money',
            'requestNum',
            'madeTo',
            'reason',
            'district',
            'zone',
            'needsStr',
            'remark',
            'selectType',
            'addTime',
            'btSn as bountySn',
            'detail'
        );
        $info=$BountyObj->getInfo($bWhere,$fields);
        if(!$info){
            $this->error(2500);
        }
         */
        
        $infoList=$BountyObj->getBountyTaskByUserId($this->userId,2,1);
        if(!$infoList)
            $this->error(2500);
        
        $infoData = array_shift($infoList);
        $info = array(
            'name' => $infoData['name'],
            'money' => $infoData['money'],
            'requestNum' => $infoData['requestNum'],
            'madeTo' => $infoData['madeTo'],
            'reason' => $infoData['reason'],
            'district' => $infoData['district'],
            'zone' => $infoData['zone'],
            'needsStr' => $infoData['needsStr'],
            'remark' => $infoData['remark'],
            'selectType' => $infoData['selectType'],
            'addTime' => $infoData['addTime'],
            'detail'  => $infoData['detail'],
            'bountySn' => $infoData['btSn'],
        );
        $info['madeTo']=$info['madeTo']?$BountyObj->madetoArr[$info['madeTo']]:'';
        $info['reason']=$info['reason']?$BountyObj->reasonArr[$info['reason']]:'';

        $dis='深圳全城';
        if($info['district']){
            /*
            $disInfo=M('town')->field('tname')->where('tid='.$info['district'])->find();
            
            $disInfo=D('Town')->getTown($info['district']); 
            if($disInfo['tname']){
                $dis=$disInfo['tname'];
            }
             */
            $dis = D('Town')->getTown($info['district']);
        }
        $area='全区';
        if($info['zone']){
            /*
            $areaInfo=M('salon_area')->field('areaname')->where('areaid='.$info['zone'])->find();
            if($areaInfo['areaname']){
                $area=$areaInfo['areaname'];
            }
             */
            $area=D("SalonArea")->getAreaName($info['zone']);
        }
        unset($info['district']);
        unset($info['zone']);

        $info['area']=$dis.','.$area;

        if($info['selectType']==1){
            $info['selectType']='自己选择设计师';
        }else if($info['selectType']==2){
            $info['selectType']='臭美代选';
        }else if($info['selectType']==3){
            $info['selectType']='is wrong';
        }
        $info['addTime']=date('Y-m-d H:i',$info['addTime']);
        if($info['detail']){
            $detail=json_decode($info['detail'],true);
            if($detail){
                foreach($detail as &$detailV){
                    $detailV['reason']=$detailV['reason']?$BountyObj->reasonArr[$detailV['reason']]:'';
                    $detailV['remark']=$detailV['remark']?:'无';
                }
            }

        }else{
            $detail[]=array(
                'reason'=>$info['reason'],
                'name'=>$info['name'],
                'needsStr'=>$info['needsStr'],
                'remark'=>$info['remark']?:'无',
            );
        }
        $info['detail']=$detail;

        //print_r($info['detail']);
        $this->ret['main']=$info;
        $this->success();
    }

    
    /*
     * 抢单造型师列表
     * @author huliang
     */
    public function stylistList() {
        $paramData = $this->param;
        $bountySn = $paramData['bountySn'];
        $page = empty( $paramData["page"] ) ? 1 : $paramData["page"];
        $pageSize = empty($paramData["pageSize"]) ? 10 : $paramData["pageSize"] ;
        
        if(empty($bountySn)){
            $this->error(3003);
        }
        /**
          $taskInfo = M('bounty_task')->field('requestNum,money,district,zone,detail')->where("btSn = '%s' ",$bountySn)->find(); 
         */
        $taskInfo = D('Bounty')->getBountyTaskBybtSn($bountySn);
        //echo M()->getLastSql();exit;
        //print_r($taskInfo);exit;
        $money = intval($taskInfo['money']);
        $requireNum = intval($taskInfo['requestNum']);
        $district = intval($taskInfo['district']);
        $zone = intval($taskInfo['zone']);
        //根据抢单金额获取通知造型师总人数       
        //获取单人金额
        if(empty($taskInfo['detail'])){
            $count =1;
        }else{
            $count = count(json_decode($taskInfo['detail'],true));
        }
        $oneMoney = floor($money/$count);
        $stylistInfo = D('Bounty')->informStylist($oneMoney,$district,$zone);
        //print_r($stylistInfo);exit;
        $matchNum = count($stylistInfo);
        //获取抢单造型师列表信息
        $where = array('btSn'=>$bountySn);
        //获取发型师抢单信息
        /**
         $requestListInfo = M('bounty_request')->where($where)->order('addTime desc')->page($page,$pageSize)->select(); 
         */
        $requestListInfo = D('BountyRequest')->getBountyRequestList($bountySn,$page-1,$pageSize);
        if(!$requestListInfo){
            $this->error(3001);
        }
        foreach ($requestListInfo as $requestInfo) {
            //造型师留言
            $remark = $requestInfo['remark'];
            //发型师id
            $hairstylistId = $requestInfo['hairstylistId'];
            //获取造型师和所在店铺信息
            /*
            $res = D('StylistAndSalonView')->field('salonname,district,zone,stylistId,stylistName,stylistImg,grade')->where('stylistId ='.$hairstylistId)->find();
             */
            $res = D('HairStylist')->getHairstylist($hairstylistId);
            $salon = D('Salon')->getSalonById($res['salonId']);
            $res['salonname'] = $salon['salonname'];
            //print_r(D()->getLastSql());exit;
            //取出grade等级对应的值
            $res['gradeValue'] = D('Category')->getStylistCatName($res['grade']);
            unset($res['grade']);
            //取出区域和商圈对应的值
            $res['district'] = $salon['district'];
            $res['zone'] = $salon['zone'];
            $districtAndZone = D('salon')->getTownAndAreaName($res['district'],$res['zone']); 
            $res['district'] = $districtAndZone['townName'];
            $res['zone'] = $districtAndZone['areaName'];
            $res['remark'] = $remark;
            /**
            //打赏次数,根据satisfyType查询
            $where1 = array('hairstylistId' => $hairstylistId ,'satisfyType' => $this->_isSatisfy);
            $count1 = M('bounty_task')->where($where1)->count();
            //用户取消打赏次数,就是用户不满意
            $where2 = array('hairstylistId' => $hairstylistId ,'satisfyType' => $this->_unSatisfy);
            $count2 = M('bounty_task')->where($where2)->count();
             */
            //打赏次数,根据satisfyType查询
            $count1 = D('Bounty')->getHairstylistBountyCount($hairstylistId,$this->_isSatisfy);
            //用户取消打赏次数,就是用户不满意
            $count2 = D('Bounty')->getHairstylistBountyCount($hairstylistId,$this->_unSatisfy);
            
            //满意度
            $countAll = $count1 + $count2;
            if(empty($countAll)){
                $res['goodScale'] = '100%';
            }else{
                $res['goodScale'] = round( $count1/$countAll, 4 ) * 100 . '%';
            }
            $bountyStylistListInfo[] = $res;
        }        
        //将返回结果综合
        $Info = array(
            'matchNum' => $matchNum,
            'requireNum' => empty($requireNum)? 0:$requireNum,
            'stylistList' => empty($bountyStylistListInfo)? array():$bountyStylistListInfo,
        );
        $this->ret['main'] = $Info;
        $this->success();
    }
    /*
     * 选择造型师
     * @author huliang
     */
    public function chooseStylist(){
        //必传stylistId，bountySn，userId
        $paramData = $this->param;
        $bountySn = $paramData['bountySn'];
        
        if(empty($this->userId)){
            $this->error(1);
        }
        if(empty($paramData['bountySn'])){
            $this->error(3003);
        }
        if(empty($paramData['stylistId'])){
            $this->error(3004);
        }
        /**
         * 更新bounty_task 和 bounty_request表
         */
        //根据造型师id找到店铺名
        /*
        $stylistInfo = M('hairstylist')->field('salonId,osType')->where("stylistId = %d",$paramData['stylistId'])->find();
         */
        $stylistInfo = D('HairStylist')->getHairstylist($paramData['stylistId']);
        $salonId = $stylistInfo['salonId'];
        $data = array(
           'btSn' =>$paramData['bountySn'],
           'salonId' => $salonId,
            'hairstylistId' => $paramData['stylistId'],
            'btStatus' => 2,
            'selectTime' => time()           
        );
        //更新bounty_task
        /*
        $model = M('bounty_task');
        $model->startTrans();
        $res1 = $model->where("btSn = '%s'",$paramData['bountySn'])->save($data);
         */
        $model = D('Bounty');
        $model->updateStylistSelectedBySn($data);
        //echo M()->getLastSql();exit;
        if($res1 === false){
            $model->rollback();
            $this->error(3005);
        }
        //根据btSn，hairstylistId更新bounty_request中的状态
        /**
        $where = array('btSn' => $paramData['bountySn'],'hairstylistId' => $paramData['stylistId']);
        $res2 = M('bounty_request')->where($where)->save(array('brStatus' => 2));
         */
       $res2 = D('BountyRequest')->updateBountyReqBrStatus($bountySn,$paramData['stylistId'],2);
        if($res2 === false){
            $model->rollback();
            $this->error(2509);
        }
        //更新salon_fundflow表
        /*
        $bargainno = M('salon')->where('salonid ='.$salonId)->getField('bargainno');
         */
        $salonInfo = D('Salon')->getSalonById($salonId);
        $bargainno = $salonInfo["bargainno"];
        if(empty($bargainno)){
            $model->rollback();
            $this->error(2008);
        }
        /*
        $res4 = M('salon_fundflow')->where("ordersn = '%s'",$paramData['bountySn'])->save(array('salonid' => $salonId, 'bargainno' => $bargainno ));
         */
        $res4 = D("SalonFundflow")->updateSalonFundflowByOrderSn($bountySn, $salonId, $bargainno);
        if($res4 === false){
            $model->rollback();
            $this->error(2513);
        }
        //更新bounty_push表
        //选中的用户更新为已选中
        /*
        $pushWhere1 = array(
            'btSn' => $paramData['bountySn'],
            'stylistId' => $paramData['stylistId'],
            'reqStatus' => 1,
        );
        $res5 = M('bounty_push')->where($pushWhere1)->save(array('reqStatus' => 3));
         */
        $res5 = D("BountyPush")->updateReqStatus($bountySn,3,$paramData['stylistId']);
        if(!$res5){
            $model->rollback();
            $this->error(2518);
        }
        //未选中的用户更新为用户选择其他造型师
        /*
        $pushWhere2 = array(
            'btSn' => $paramData['bountySn'],
            'reqStatus' => 1,
        );
        $res6 = M('bounty_push')->where($pushWhere2)->save(array('reqStatus' => 4));
         */
        $res6 = D("BountyPush")->updateReqStatusBySnAndReqS($bountySn, 1,4);
        if($res6 === false){
            $model->rollback();
            $this->error(2519);
        }
        //未申请的用户没有被选中
        /*
        $pushWhere3 = array(
            'btSn' => $paramData['bountySn'],
            'reqStatus' => 0,
        );
        $res7 = M('bounty_push')->where($pushWhere3)->save(array('reqStatus' => 2));
         */
        $res7 = D("BountyPush")->updateReqStatusBySnAndReqS($bountySn, 0,2);
        if($res7 === false){
            $model->rollback();
            $this->error(2522);
        }
        $model->commit();
        //将消息推送给此人
        $phoneType = $stylistInfo['osType'];
        $stylistId = $paramData['stylistId'];
        $type = 2;
        $stylistIdList = array(
            'android' => array($stylistId),
            'ios' => array($stylistId),
        );
        $info = array(
            'bountySn' => $paramData['bountySn'],
            'stylistIdList' => $stylistIdList,
        );
        $this->allTypeMessage($info,$type,$phoneType);
        $this->success();
    }

    /*
     * 造型师评价列表
     * @author huliang
     */
    public function stylistComment(){
        $paramData = $this->param;
        $stylistId = $paramData['stylistId'];
        if(empty($stylistId)){
            $this->error(1);
        }
        $page = empty( $paramData["page"] ) ? 1 : $paramData["page"];
        $pageSize = empty($paramData["pageSize"]) ? 10 : $paramData["pageSize"] ;
        $totalNum = $paramData["totalNum"];
        
        //在comment表中通过造型师id找到用户评论信息
        /*
        $field = array('userId','content','imgSrc','addTime');
        $where['hairstylistId'] = $stylistId;
        $where['type'] = 2;
        $userInfoList = M('bounty_comment')->field($field)->where($where)->order('addTime desc')->page($page,$pageSize)->select();
         */
        $comments=D('BountyComment')->getBtCommentByStylistId($stylistId,$page-1,$pageSize);
        $userInfoList = array();
        foreach($comments as $comment)
        {
            if($comment['type'] == 2) 
                $userInfoList[] = $comment;
        }
        //echo M()->getLastSql();exit;
        foreach ($userInfoList as $userInfoDetail) {
            //通过用户id获取用户姓名，头像
            /*
            $info = M('user')->field('nickname,img')->where('user_id ='.$userInfoDetail['userId'])->find();
             */
            $info = D('User')->getUserById($userInfoDetail['userId']);
            if(empty($info))
                continue;
            $userLever = D('UserLevel')->getLevelByUid($userInfoDetail['userId']);
            //拼接成一个数组
            $res = array(
                'userName' => $info['nickname'],
                //V5.4.2 新增，将昵称改成加密的手机号显示
                'mobilephone' => is_null($info['mobilephone']) ? '': substr_replace($info['mobilephone'], '****', 3, 4),
                'level' => $userLever,
                'userImg'  => $info['img'],
                'content'  => $userInfoDetail['content'],
                'imgSrc'   => json_decode($userInfoDetail['imgSrc'],true),
                'addTime'  => date("Y-m-d",$userInfoDetail['addTime'])
            );
            $userInfos[] = $res; 
        }
        /*
         $count = M('bounty_comment')->where("hairstylistId = %d",$stylistId)->count();
         */
         $count = D('BountyComment')->getBtCommentCountByStylistId($stylistId);
        if( empty($totalNum) )
            $this->ret["other"]["totalNum"] = intval($count);
        else
            $this->ret["other"]["totalNum"] = $totalNum;

        if( empty( $this->ret["other"]["totalNum"] ) )
            $this->success( $this->ret );
        $this->ret["main"] = $userInfos;
        $this->success();
       
    }

    /**
     * 发布任务接口
     * By zhigui.zhang@choumei.cn
     * Date 2015-05-15
     */
    public function pubBountyTask(){
        if(empty($this->userId)){
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
        $data['remark'] = isset($paramData['remark'])?$paramData['remark']:"";
        $data['addTime'] = time();

        if(!$paramData['money'] || !$paramData['needsStr'] || !$paramData['name'] || !$paramData['madeTo'] || !$paramData['reason'] || !isset($paramData['district']) || !isset($paramData['zone']) || !$paramData['selectType'] ) $this->error(1);
        if(intval($paramData['money']) < 200){
            $this->error(2515);
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
        
        $bountyInfo=$BountyObj->getBountyTaskByUserId($this->userId,2,1);
        if (is_null($bountyInfo)) {
            $count = 0;
        }
        if($count){
            $this->error(2514);
        }
        //判断needsStr是否在可选范围内
        $allNeedsStr = array('洗剪吹','染发','烫发','接发','护发','到店商议');
        $needsArr = explode(",",$paramData['needsStr']);
        //求交集
        if ($needsArr != array_intersect($needsArr, $allNeedsStr)) { 
            $this->error(2512);
        }           
        //如果金额，商圈和区域选出来的造型师不存在，则重新发布
        //根据抢单金额获取符合条件的造型师
        if($paramData['selectType'] == 2){
           $stylistInfo = D('Bounty')->informStylist(intval($paramData['money']),$data['district'],$data['zone']);
            if(empty($stylistInfo)){
                $this->error(2520);
            } 
        }        
        $bountyObj = D('Bounty');
        $bountyObj->startTrans();
        /*
        $btSn = $bountyObj->addBounty( $data );
         */
        $btSn = $bountyObj->addBounty( $this->userId,intval($paramData['money']),$paramData['needsStr'],$paramData['name'],$paramData['madeTo'],$paramData['reason'],$paramData['district'],$paramData['zone'], $paramData['selectType'],isset($paramData['remark'])?$paramData['remark']:"",time());
        if($btSn === false){
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
            'bountySn'  => $btSn,
        );

        $this->ret["main"] = $main;
        $this->success();

    }

    /**
     * 支付成功界面  增加闺蜜团的功能 
     * By zhigui.zhang@choumei.cn  huliang修改
     * @date 2015-05-15
     */
    public function paySuccess()
    {
        if(empty($this->userId)){
            $this->error(1);
        }
        $paramData = $this->param;
        $bountySn = $paramData['bountySn'];
        if(!$bountySn) $this->error(3003);
        /*
         $taskInfo = M('bounty_task')->field('hairstylistId,money,district,zone,selectType,detail')->where("btSn = '%s'",$bountySn)->find();
         */
        $taskInfo = D('Bounty')->getBountyTaskBybtSn($bountySn);
        //echo M()->getLastSql();exit;
        $stylistId = $taskInfo['hairstylistId'];
        $money = $taskInfo['money'];
        $district = $taskInfo['district'];
        $zone = $taskInfo['zone'];
        $selectType = $taskInfo['selectType'];
        if($selectType == 1){
            //自己选
            //根据抢单金额获取通知造型师总人数    
            //获取单人金额
            if(empty($taskInfo['detail'])){
                $count =1;
            }else{
                $count = count(json_decode($taskInfo['detail'],true));
            }
            $oneMoney = floor($money/$count);
            $stylistInfo = D('Bounty')->informStylist($oneMoney,$district,$zone);
            $num =count($stylistInfo);       
            $main = array(
                'hairstylistNum' => $num
            );
        }else if($selectType == 2){
            $main = array(
                'stylistId' => $stylistId
            );           
        }else if($selectType == 3){
            $main = array(
                'stylistId' => $stylistId
            );           
        }else{
            $this->error(3006);
        }    
        $this->ret["main"] = $main;
        $this->success();
    }

	/**
	*管理后台订单结算
	**/
	private function makeToken(&$params)
    {
        asort($params);
        $url = http_build_query($params);
        $params['token'] =  md5(md5($url).self::TOKEN_KEY);
    }
	
    /**
     * 打赏功能
     * By zhigui.zhang@choumei.cn
     * @date 2015-05-15
     */
    public function reward()
    {
        if(empty($this->userId)){
            $this->error(1);
        }

        $paramData = $this->param;
        $data = array();
        $data['userId'] = $this->userId;
        $data['btSn'] = $paramData['bountySn'];
        $data['hairstylistId'] = intval($paramData['hairstylistId']);
        $data['type'] = $this->_comment_unsatisfy;
        //$data['salonId'] = intval($paramData['salonId']);
        $data['imgSrc'] = empty($paramData['imgSrc'])? "":json_encode($paramData['imgSrc'], JSON_UNESCAPED_UNICODE);
        $satisfyType = $paramData['satisfyType'];
        $data['notSatisfyStr'] = $paramData['satisfyRemark'];
        $data['addTime'] = time();
        $data['content']='';
        $bountyObj = D('Bounty');
        //根据造型师找出造型师手机类型
        /*
        $osType = M('hairstylist')->where('stylistId = %d',$paramData['hairstylistId'])->getField('osType');
         */
        $hairstylist = D('HairStylist')->getHairstylist($paramData['hairstylistId']);
        $osType=$hairstylist["osType"];
        if($satisfyType == 1){  //类型为满意
            $res = $bountyObj->modiBountyTask($data['btSn']);
            //如果满意，通知该造型师
            if($res){
                //将消息推送给此人
                $phoneType = $osType;
                $stylistId = intval($paramData['hairstylistId']);
                $type = 3;
                $stylistIdList = array(
                    'android' => array($stylistId),
                    'ios' => array($stylistId),
                );
                $info = array(
                    'bountySn' => $paramData['bountySn'],
                    'stylistIdList' => $stylistIdList,
                );
                $this->allTypeMessage($info,$type,$phoneType);
				
				//新管理后台订单结算功能（朱念需求）
				$adminSendData = array("type"=>2,"ordersn"=>$data['btSn']);
				$this->makeToken($adminSendData);
				$adminUrl = C('MANAGER_URL');
				$adminRetrun = curlPost($adminUrl,$adminSendData);//发送数据到后台
				if($adminRetrun)
				{
					$adminRetArr = json_decode($adminRetrun,true);
					if(!$adminRetArr["result"])
					{
						Log::write("sendOrderTickeData:".json_encode($adminSendData));
						Log::write("sendOrderTicke:".$adminRetrun);
					}
				}
				
            }
        }elseif($satisfyType){  //类型为不满意
            $res = $bountyObj->addBountyComment($data);
            if($res){
                //将消息推送给此人
                $phoneType = $osType;
                $stylistId = intval($paramData['hairstylistId']);
                $type = 4;
                $stylistIdList = array(
                    'android' => array($stylistId),
                    'ios' => array($stylistId),
                );
                $info = array(
                    'bountySn' => $paramData['bountySn'],
                    'stylistIdList' => $stylistIdList,
                );
                $this->allTypeMessage($info,$type,$phoneType);
            }
            //v5.4.2不满意时，增加一条短信
            $bountyInfoBySn = D('Bounty')->getBountyTaskBybtSn($paramData['bountySn']);
            //v5.4.2 取消任务发送一条短信
            $smsText = C('SMS_C');
            $repalceArray = array(
                'bountyPay' => $bountyInfoBySn['money'],
            );
            $smsText = D('Sms')->repalceSms($smsText,$repalceArray);
            $userInfo = D('User')->getUserById($this->userId);
            $mobilephone = $userInfo['mobilephone'];
            D('Sms')->sendSmsByType($mobilephone, $smsText,4);
            
        }
        if(!$res){
            $this->error($bountyObj->getError());
        }
        $this->success();
    }
	
	/***
	* 任务详情
	* @lufangrui
	* @2015-05-16
	***/ 
	public function taskDetail(){
		$params = $this->param;
		$bountySn = $params['bountySn'];
		$userId = $this->userId;
		// 校验参数
		if( empty($bountySn) || empty($bountySn) )
			$this->error( 1 );
		$bountyObj = D('Bounty');
        /*
		$info = $bountyObj->getBountyTask($bountySn,$userId);
         */
        $bountyInfo = $bountyObj->getBountyTaskBybtSn($bountySn);
        if(empty($bountyInfo) || $bountyInfo['userId'] != $userId)
            $info = null;
        else
            $info = $bountyInfo;
		// 未找到赏金任务信息
		if( empty($info) )
			$this->error(2506);
		// 初始化返回信息
		$this->ret = array(
			'main' => array(
				'bountyInfo'=>(object)array()
			),
			'other' => array(
				'status'=>2,
				'hairstylistStatus'=>2
			)
		);
		// 这个时间是根据任务状态而选择相应的时间
		$statusAndTime = array(
			2 => $info['selectTime'], // 待服务时间为选择造型师的时间
			3 => $info['serviceTime'], // 已服务时间为服务完成的时间
			4 => $info['serviceTime'], // 已服务时间为服务完成的时间
		);
		$selectType = array(
			'',
			'自己选择设计师',
			'臭美代选'
		);
		// 增加对闺蜜团的支持 任务类型: 1单个 2闺蜜团
		$this->ret['other']['taskType'] = intval( $info['taskType'] );
		$this->ret['other']['status'] = intval($info['btStatus']);
		$this->ret['other']['hairstylistStatus'] = empty( $info['hairstylistId'] ) ? 1 : 2;
		// 获取区域信息
		$townModel = D('Town');
		$district = $townModel->getTown( $info['district'] );
		if( !empty($district) ){
			$areaModel = D('SalonArea');
			$areaName = $areaModel->getAreaName( $info['zone'] );
                        $areaName=  empty($info['zone'])?'不限':$areaName;
		}
		// 赏金任务信息
		$bountyInfo['name'] = $info['name'];
		$bountyInfo['money'] = $info['money'];
		$bountyInfo['madeTo'] = empty($bountyObj->madetoArr[ $info['madeTo'] ]) ? '无' : $bountyObj->madetoArr[ $info['madeTo'] ];
		$bountyInfo['reason'] = empty($bountyObj->reasonArr[ $info['reason'] ]) ? '无' : $bountyObj->reasonArr[ $info['reason'] ];
		$bountyInfo['serviceZone'] = empty($district) ? '全城' : $district . ' - ' .$areaName;
		$bountyInfo['needsStr'] = empty($info['needsStr'])? '无' : $info['needsStr'];
		$bountyInfo['remark'] = empty($info['remark'])? '无' : $info['remark'];
		$bountyInfo['selectHairstylist'] = $info['selectType']!=3 ? $selectType[ $info['selectType'] ] : '';
		$bountyInfo['selectType'] = intval($info['selectType']);
		$bountyInfo['startTime'] = date('Y-m-d H:i:s',$info['addTime']);
		$bountyInfo['serviceTime'] = empty($statusAndTime[ $info['btStatus'] ])? '' : date('Y-m-d H:i:s',$statusAndTime[ $info['btStatus'] ]  );
		$bountyInfo['selectTime'] =  !empty($info['selectTime'])?date('Y-m-d H:i:s',$info['selectTime']):'';
                $bountyInfo['endTime'] = empty($info['endTime'])? '' : date('Y-m-d H:i:s',$info['endTime']);
		$bountyInfo['bountySn'] = $bountySn;
		$bountyInfo['isComment'] = intval($info['isComment']);
		$bountyInfo['requestNum'] = intval($info['requestNum']);
		$bountyInfo['salonId'] = intval($info['salonId']);
		// 如果为闺蜜任务时
		if( $this->ret['other']['taskType'] == $this->_taskType[1] ){
			$girlFriend = json_decode( $info['detail'] , true );
			foreach( $girlFriend as $key => $val ){
				if(array_key_exists('reason',$val))
					$girlFriend[$key]['reason'] = empty($val['reason']) ? '无' : $bountyObj->reasonArr[ $val['reason'] ];
				if(array_key_exists('madeTo',$val))
					$girlFriend[$key]['madeTo'] = empty($bountyObj->madetoArr[ $val['madeTo'] ]) ? '无' : $bountyObj->madetoArr[ $val['madeTo'] ];
				if(array_key_exists('needsStr',$val))
					$girlFriend[$key]['needsStr'] = empty($val['needsStr'])? '无' : $val['needsStr'];
				if(array_key_exists('remark',$val))
					$girlFriend[$key]['remark'] = empty($val['remark'])? '无' : $val['remark'];
			}
			$this->ret['main']['girlFriend'] = empty($girlFriend) ? array() : $girlFriend;
		}
		// 打赏金额 即当状态为已打赏时 而且打赏类型为满意时打赏全部金额
		$bountyInfo['getMoney'] = ( $info['btStatus'] == 4 && $info['satisfyType'] == 1 ) ? $info['money'] : 0 ;
		
		$this->ret['main']['bountyInfo'] = $bountyInfo;
		// 为代服务状态时直接返回信息
		if( in_array($info['btStatus'] , array(1,9)) )
			$this->success();
		$userModel = D('user');
		// 发型师信息
		$stylistId = $info['hairstylistId'];
		$salonId = $info['salonId'];
		
		$hairstylistModel = D('HairStylist');
		$salonModel = D('Salon');
		
		$stylistDes = $hairstylistModel->getHairstylist( $stylistId );
		$salonName = $salonModel->getSalon( $salonId );
        if($info['selectType']==3){
              $bountyInfo['selectHairstylist']=!empty($stylistDes)?$stylistDes['stylistName']:'';
        }
		$salonName = empty( $salonName ) ? '' : $salonName['salonname'];
		$hairstylistInfo['stylistName'] = $stylistDes['stylistName'];
		$hairstylistInfo['stylistImg'] = $stylistDes['stylistImg'];
		$hairstylistInfo['job'] = $this->grade[ $stylistDes['grade'] ];
		$hairstylistInfo['stylistId'] = intval( $stylistId );
		$hairstylistInfo['salonName'] = $salonName;
		$this->ret['main']['hairstylistInfo'] = $hairstylistInfo;
        $this->ret['main']['bountyInfo'] = $bountyInfo;
		// 如果已评价那么还需要取出评价信息
		if( !(in_array($info['isComment'],array(2)) && $info['btStatus'] == 4 ) )
			$this->success();
		
		// 评论信息
		$bountyCommentModel = D('BountyComment');
		$userLevel = D("UserLevel")->getLevelByGrowth( $this->userInfo['growth'] );
		$bountyCommentDes = $bountyCommentModel->getComment( $bountySn,$userId );
		
		$appraisal['timer'] = date('Y-m-d H:i:s',$bountyCommentDes['addTime']);
		$appraisal['level'] = intval( $userLevel );
		$appraisal['content'] = $bountyCommentDes['content'];
		$appraisal['appraisalImgList'] = empty($bountyCommentDes['imgSrc']) ? '' : json_decode($bountyCommentDes['imgSrc'],true);
		$appraisal['headPortrait'] = $this->userInfo['img'];
		$appraisal['name'] = $this->userInfo['nickname'];
        //V5.4.2 新增，将昵称改成加密的手机号显示
        $appraisal['mobilephone'] = is_null($this->userInfo['mobilephone']) ? '': substr_replace($this->userInfo['mobilephone'], '****', 3, 4);
		
		$this->ret['main']['appraisal'] = $appraisal;
		$this->success();
	}
	
	/***
	* 打赏记录列表
	* @lufangrui
	* @2015-05-16
	***/ 
	public function rewardList(){
		$params = $this->param;
		
		$userId = $this->userId;
		$commentStatus = empty($params['commentStatus']) ? 1 : $params['commentStatus'];
		$page = empty($params['page']) ? 1 : $params['page'];
		$pageSize = empty($params['pageSize']) ? 10 : $params['pageSize'];
		$totalNum = $params['totalNum'];
		
		if( empty($userId) )
			$this->error( 1 );
		if( !in_array($commentStatus,array(1,2)) )
			$this->error( 1 );
		if( $pageSize >20 )
			$pageSize = 20;
		
		// 初始化返回信息
		$this->ret = array(
			'main'=>array(),
			'other'=>array(
				'noAppraisalNum' => 0,
				'appraisalNum' => 0
			)
		);
		$salonModel = D('Salon');
		$bountyObj = D('Bounty');
		$hairstylistModel = D('HairStylist');
		$count = $bountyObj->taskCommentCount( $userId );
		
		if( empty( $count ) )
			$this->success();
		// 当返回的不为空的时候
		if( !empty($totalNum) ){
			if( $commentStatus == 1 ){
				$noAppraisalNum = $totalNum;
				$appraisalNum = $count['appraisalNum'];
			}else{
				$noAppraisalNum = $count['noAppraisalNum'];
				$appraisalNum = $totalNum;
			}
		}else{
			$noAppraisalNum = $count['noAppraisalNum'];
			$appraisalNum = $count['appraisalNum'];
		}
		$this->ret['other']['noAppraisalNum'] = intval($noAppraisalNum);
		$this->ret['other']['appraisalNum'] = intval($appraisalNum);
		// 拿到赏金任务信息列表
       /*
        $list = $bountyObj->taskList( $userId,$commentStatus,$page,$pageSize );
        */
		$list = $bountyObj->taskList( $userId,$commentStatus,$page-1,$pageSize );
        
		// 根据赏金任务中取得的造型师id对应去取造型师信息
		foreach($list as $key => $val ){
			$stylistDes = $hairstylistModel->getHairstylist( $val['hairstylistId'] );
            /*
            $salonName = $salonModel->getSalon( $val['salonId'] );
            */
			$salonName = $salonModel->getSalonById( $val['salonId'] );
		
			$salonName = empty( $salonName ) ? '' : $salonName['salonname'];
			$list[$key]['stylistName'] = $stylistDes['stylistName'];
			$list[$key]['stylistImg'] = $stylistDes['stylistImg'];
			$list[$key]['job'] = $this->grade[ $stylistDes['grade'] ];
			$list[$key]['stylistId'] = intval($val['hairstylistId']);
			$list[$key]['salonName'] = $salonName;
			
			$list[$key]['money'] = $val['money'];
			$list[$key]['getMoney'] = ( $val['satisfyType'] == 1 ? $val['money'] : 0) ;
			$list[$key]['bountySn'] = $val['btSn'];
			$list[$key]['taskType'] = intval($val['taskType']);
			
			unset( $list[$key]['hairstylistId'] );
			unset( $list[$key]['salonId'] );
			unset( $list[$key]['satisfyType'] );
			unset( $list[$key]['btSn'] );
		}
		$this->ret['main'] = empty($list) ? array() : $list;
		
		$this->success();
	}
	
	/***
	* 评论功能
	* @lufangrui
	* @2015-05-16
	***/ 
	public function comment(){
		$params = $this->param;
		
		$where['userId'] = $data['userId'] = $this->userId;
		$where['btSn'] = $data['btSn'] = $params['bountySn'];
		$where['type'] = 2;
		$data['hairstylistId'] = empty($params['hairstylistId']) ? 0 : $params['hairstylistId'];
		$data['content'] = $params['content'];
		$data['imgSrc'] = $params['imgSrc'];
                $data['notSatisfyStr']='';
		$data['type'] = 2; // 评论类型 1:用户打赏不满意信息 2:用户评论
		$data['addTime'] = time();
		
		if( empty($where['userId']) || empty($where['btSn']) )
			$this->error(1);
		// 查找评论记录是否存在
		$bountyObj = D('Bounty');
		$bountyCommentModel = D('BountyComment');
        /*
		$existsComment = $bountyCommentModel->commentExists($where);
        */
        $bountyComment = D('BountyComment')->getCommentBySn($data['btSn'],2);
        if($bountyComment['userId'] == $data['userId']) 
            $existsComment = true;//表示已经评论过了
		unset( $where['type'] );
        /*
		if( !empty($existsComment) )
        */
        if($existsComment)
			$this->error( 2501 ); // 评论存在的时候就声明已经评论过
		$where['btStatus'] = 4;
		// 查找此项目是否为已打赏的状态
                /*
		$isStatus4 = $bountyObj->tastExists( $where );
                 */
                $task = D('Bounty')->getBountyTaskBybtSn($data['btSn']);
                if(empty($task) || $this->userId != $task['userId'])
                    $isStatus4 = 0;
                else
                    $isStatus4 = 1;
        
		if( empty($isStatus4) ) 
			$this->error( 2502 ); // 查找此项目为非已打赏的状态
		// 添加评论表
		$res = $bountyCommentModel->addComment( $data );
		
		$this->ret['main']['info'] = '评价失败';
		if( !empty($res) ){
			$this->ret['main']['info'] = '评价完成';
			// 更改评论状态
                        /*
                        $bool = $bountyObj->modifyComment( $data['btSn'] , $data['userId'] );
                         */
			$bool=D('Bounty')->modifyComment($data['btSn'] , $data['userId']);
		}
			
		
		$this->success();
	}


    /**
     * 赏金排行
     * @author carson
     */
    public function ranklist(){
        $type=$this->param['type'];

        $BountyObj = D('Bounty');

        if($type==2){
            //总榜
            $list=$BountyObj->getTotalRanklist();
        }else{
            //单榜
            $list=$BountyObj->getSingleRanklist();
        }
        $this->ret['main']='';
        if($list){
            foreach($list as $listK=>&$listV){
                if($listK<3){
                    $noArr=array('冠军','亚军','季军');
                    $noStr=$noArr[$listK];
                }else{
                    $noStr=$listK+1;
                }
                $listV['nameStr']=msubstr($listV['nameStr'],0,1).'***'.msubstr($listV['nameStr'],-1,1);
                $listV['noStr']=$noStr;
                $listV['money']='￥'.$listV['money'];
            }
            $this->ret['main']=$list;
        }
        $this->ret['other']['type']=$type;
        $this->ret['other']['title']='截止'.date('Y年m月d日',time()-86400);
        $this->success();
    }

    
    /*
     * @author huliang
     * 根据区域返回商圈 ，返回所有区以及对应的商圈
     */
    public function getZone(){
        
        //获取深圳市所有区
        /*
        $townList = M('town')->field('tid as tId, tname as tName')->where('iid = 1')->order('tid')->select();
         */
        $townList = array();
        $towns = D('Location')->getTownsOfCity(1);
        foreach($towns as $town)
        {
            $townList[] = array(
                'tId' => $town['id'],
                'tName' => $town['name'],
            );
        }
        if(!$townList){
            $this->error(4002);
        }
        //print_r($townList);exit;
        //所有区信息
        foreach ($townList as $key => $townValue) {
            $townInfo[$key] = $townValue;
            //根据区id获取对应商圈
            $where = array('parentid' => $townValue['tId']);
            /*
            $townInfo[$key]['areaInfo'] = M('salon_area')->field('areaid as areaId,areaname as areaName')->where($where)->order('areaid')->select();
             */
            $salonAreas = D("SalonArea")->getAreaByParentId($townValue['tId']);
            $areas = array();
            foreach($salonAreas as $salonArea)
            {
                $areas[] = array(
                    'areaId' => $salonArea['id'],
                    'areaName' => $salonArea['name'],
                );
            }
            $townInfo[$key]['areaInfo'] = $areas;
            array_unshift($townInfo[$key]['areaInfo'], array('areaId' => "0", 'areaName' => '不限'));
        }
        //增加全城全区
        array_unshift($townInfo, array('tId' => "0",'tName' => '全城','areaInfo' => array(array('areaId' => "0", 'areaName' => '不限'))));
        $this->ret['main']['townInfo'] = $townInfo;
        $this->success();
       
    }


    /**
     * 取消任务
     * @author carson
     */
    public function cancelTask(){
        $bountySn=$this->param['bountySn'];

        $BountyObj = D('Bounty');

        if(!$bountySn){
            $this->error(1);
        }
        /*
        $where['btSn']=$bountySn;
        $where['userId']=$this->userId;
        $where['isPay']=2;
        $where['btStatus']=array('lt',3);
        $where['refundStatus']=array('lt',5);
        $bountyInfo=$BountyObj->where($where)->find();
         */
        $bountyInfo=$BountyObj->getNotServeBountyTask($bountySn,$this->userId);
        if(!$bountyInfo){
            $this->error(2507);
        }
        
        /*
        $data['btStatus']=9;
        $data['endTime']=time();
        $data['refundStatus']=5;
        $rs=$BountyObj->where($where)->save($data);
         */
        $rs = $BountyObj->cancelTask($bountySn,$this->userId);
        if(!$rs){
            $this->error(2507);
        }
        /*已取消任务，未抢单更新为未抢单且未选中*/
        /**
        $sbRs=M('bounty_push')->where(array('btSn' => $bountySn, 'reqStatus' => 0))->save(array("reqStatus" => 2));
         */
        $sbRs=D('BountyPush')->updateReqStatusBySnAndReqS($bountySn,0,2);
        /*已取消任务，已抢单更新为已抢单且未选中*/
        /**
        $sbRs=M('bounty_push')->where(array('btSn' => $bountySn, 'reqStatus' => 1))->save(array("reqStatus" => 5));
         */
        $sbRs=D('BountyPush')->updateReqStatusBySnAndReqS($bountySn,1,5);

        
        /*取消任务时给已选择的造型师推送个消息*/
        if($bountyInfo['hairstylistId']){
            /*
            $stylistInfo=M('hairstylist')->field('osType')->find($bountyInfo['hairstylistId']); 
             */          
            $stylistInfo=D('HairStylist')->getHairstylist($bountyInfo['hairstylistId']);
            if(!$stylistInfo){
                $this->error(3011);
            }
            $phoneType = $stylistInfo['osType'];
            $stylistId = $bountyInfo['hairstylistId'];
            $type = 5;
            $stylistIdList = array(
                'android' => array($stylistId),
                'ios' => array($stylistId),
            );
            $info = array(
                'bountySn' => $bountySn,
                'stylistIdList' => $stylistIdList,
            );
            $this->allTypeMessage($info,$type,$phoneType);
        }
        
        $bountyInfoBySn = D('Bounty')->getBountyTaskBybtSn($bountySn);
        //取消时，给自己推送一个消息
        $title = '赏金任务取消成功';
        $desc = "您在臭美发布的￥{$bountyInfoBySn['money']}赏金任务已经取消成功，请等待第三方支付平台的退款消息(一般需要3-5工作日)(点击查看详情)";
        $payloadData = array(
             'event' => 'cancelBountyTask',
             'bountySn' => $bountySn,
             'msgType' => 4,
        );
        $msgType = 4; //取消赏金任务详情 
        $logMessage = "推送取消赏金任务写入失败，赏金单号：{$bountySn}";
        D('PushMessage')->addAndPushMessage($this->userId,$title,$desc,$payloadData,$msgType,$logMessage);
        
        //v5.4.2 取消任务发送一条短信
        $smsText = C('SMS_B');
        $repalceArray = array(
            'bountyPay' => $bountyInfoBySn['money'],
        );
        $smsText = D('Sms')->repalceSms($smsText,$repalceArray);
        $userInfo = D('User')->getUserById($this->userId);
        $mobilephone = $userInfo['mobilephone'];
        D('Sms')->sendSmsByType($mobilephone, $smsText,4);

        $this->success();
    }
    /*
     * 支付完成后调用推送
     */
    public function pushMessage(){
        $bountySn=$this->param['bountySn'];
        if(!$bountySn){
            $this->error(3003);
        }
        
        /*
        $taskInfo = M('bounty_task')->field('userId,name,money,district,zone,selectType,detail,hairstylistId')->where("btSn = '%s'",$bountySn)->find();
         */
        $taskInfo = D('Bounty')->getBountyTaskBybtSn($bountySn);
        //判断bounty_task表中是否存在赏金单号，不存在写日志直接返回
        if(!$taskInfo){
            Log::write("BountySn Not Exist:".$bountySn);
            return ;
        }
        //赏金单号存在，就去push表查看，如果push表中有数据，表示已经推送过，防止同一单号多次推送
        /**
          $pushCount = M('bounty_push')->where(array('btSn' => $bountySn))->count();
         */
        $pushCount = D('BountyPush')->getBountyPushCount($bountySn);
        if($pushCount){
            Log::write("Already Push Message:".$bountySn);
            return ;
        }
        Log::write("pushMessageBountySn:".$bountySn);
        $money = intval($taskInfo['money']);
        $district = intval($taskInfo['district']);
        $zone = intval($taskInfo['zone']);
        $userId = $taskInfo['userId'];
        $userName = $taskInfo['name'];
        $selectType = $taskInfo['selectType'];
        $hairstylistId = $taskInfo['hairstylistId'];
        if(!$hairstylistId){
            //获取单人金额
            if(empty($taskInfo['detail'])){
                $count =1;
            }else{
                $count = count(json_decode($taskInfo['detail'],true));
            }
            $oneMoney = floor($money/$count);
            //根据抢单金额获取符合条件的造型师
            $stylistInfo = D('Bounty')->informStylist($oneMoney,$district,$zone);
        }
        \Think\Log::record("push stylistInfo:".print_r($stylistInfo, true));
        if($selectType == 1){
            //自己选
            //将符合条件的造型师全部写入push表
            $pushInfo = array(
                'userId' => $userId,
                'btSn' => $bountySn,
                'status' => 2,
                'reqStatus' => 0,   //默认未抢单，如果臭美代选，这里要更改已抢单，同时其他用户更改为已抢单未选中。
                'addTime' => time(),
            );
            foreach ($stylistInfo as $value) {
                $pushInfo['stylistId'] = $value['stylistId'];  
                $pushInfo['ostype'] = $value['osType'];  
                $allPushData[] = $pushInfo;
            }
            /*
            $resPush = M('bounty_push')->addAll($allPushData);
            if(!resPush){
             */
            $resPush = D('BountyPush')->addAllBountyPush($allPushData);
            if(!$resPush){
                $this->error(2601);
            }
            $this->pushMoreMessage($taskInfo,$stylistInfo,$bountySn);
        }elseif ($selectType == 2) {
            //臭美代选,选出一个造型师
            $this->pushSingleMessage($taskInfo,$stylistInfo,$bountySn);
        }elseif($selectType == 3){
            //选择服务过的造型师
            if(!$hairstylistId){
                $this->error(2526);
            }
            /*
            $myStylistInfo = M('hairstylist')->field('salonId,osType')->where(array('stylistId' => $hairstylistId ))->find();
             */
            $myStylistInfo = D('HairStylist')->getHairstylist($hairstylistId);
            $stylistInfo['stylistId'] = $hairstylistId;
            $stylistInfo['salonId'] = $myStylistInfo['salonId'];
            $stylistInfo['osType'] = $myStylistInfo['osType'];
            $this->pushToMyStylistMessage($taskInfo,$stylistInfo,$bountySn);
        }else{
            $this->error(2517);
        }
        
    }
    /**
     * 臭美代选
     */
    private function pushSingleMessage($taskInfo,$stylistInfo,$bountySn) {
        //如果臭美代选，则选出符合条件的造型师，并推送消息      
        $userId = $taskInfo['userId'];
        $userName = $taskInfo['name'];
        $money = $taskInfo['money'];
        $randNum = mt_rand(0, count($stylistInfo)-1);
        $stylistId = $stylistInfo[$randNum]['stylistId'];    
        //选出这个造型师的osType
        /*
        $model = M('hairstylist');
        $model->startTrans();
        $stylistInfo = $model->field('salonId,osType')->where('stylistId ='.$stylistId)->find();
         */
        $model = D('HairStylist');
        $stylistInfo =D('HairStylist')->getHairstylist($stylistId);
        //如果是臭美代选，支付成功后，bounty_task中的状态改为待服务,同时将造型师信息加入表中
        $stylistData['salonId'] = $stylistInfo['salonId'];
        $stylistData['hairstylistId'] = $stylistId;
        $stylistData['btStatus'] = 2;
        $stylistData['requestNum'] = 1;
        $stylistData['addTime'] = time();
        $stylistData['selectTime'] = time();
        /**
          $res1 = M('bounty_task')->where("btSn = %s",$bountySn)->save($stylistData);
         */
        $res1 = D('Bounty')->updateBountyTaskBybtSn($bountySn, $stylistInfo['salonId'],$stylistId,2,1,time(),time());
        if($res1 === false){
            $model->rollback();
            $this->error(2516);
        }
        //更新bounty_request
        $requestData['btSn'] = $bountySn;
        $requestData['salonId'] = $stylistInfo['salonId'];
        $requestData['hairstylistId'] = $stylistId;
        $requestData['brStatus'] = 2;
        $requestData['addTime'] = time();
        /**
          $res2 = M('bounty_request')->add($requestData);
         */
        $res2 =D('BountyRequest')->addBountyRequest($bountySn,$stylistInfo['salonId'],$stylistId,2,time());
        if($res2 === false){
            $model->rollback();
            $this->error(2516);
        }
               
        //添加数据到push表，如果臭美代选，只需要将选中的用户写入push表。
        /*
        $pushSingleInfo = array(
            'userId' => $userId,
            'btSn' => $bountySn,
            'status' => 2,
            'reqStatus' => 3,   //用户已选中
            'stylistId' => $stylistId,
            'ostype' => $stylistInfo['osType'],
            'addTime' => time(),
        );
         $resPush = M('bounty_push')->add($pushSingleInfo);
         */
        $resPush = D('BountyPush')->addBountyPush($userId,$bountySn,2,3,$stylistId, $stylistInfo['osType'],time());
        if(!$resPush){
            $this->error(2601);
        }       
        $model->commit();
        //将消息推送给此人
        $phoneType = $stylistInfo['osType'];
        $type = 2;
        $stylistIdList = array(
            'android' => array($stylistId),
            'ios' => array($stylistId),
        );
        $info = array(
            'bountySn' => $bountySn,
            'stylistIdList' => $stylistIdList,
        );
        $this->allTypeMessage($info,$type,$phoneType);
        /*
        $info = array(
            'userId' => $userId,
            'bountySn' => $bountySn,
            'name' => $userName,
            'money' => $money
        );
        if($stylistInfo['osType'] == 1){
            $info['stylistIdList'] = array('android' => array($stylistId));
            $this->pushAndroidMessage(1,'',$info);            
        }elseif($stylistInfo['osType'] == 2){
            $info['stylistIdList'] = array('ios' => array($stylistId));
            $this->pushIOSMessage(1,'',$info);
        }
         */
        $this->success(1);       
    }
    /**
     * 选择服务过的造型师
     */
    private function pushToMyStylistMessage($taskInfo,$stylistInfo,$bountySn) {
        $userId = $taskInfo['userId'];
        $stylistId = $stylistInfo['stylistId'];
        $salonId = $stylistInfo['salonId'];
        $osType = $stylistInfo['osType'];
        //选出这个造型师的osType
        //$stylistInfo = $model->field('salonId,osType')->where('stylistId ='.$stylistId)->find();
        //如果是臭美代选，支付成功后，bounty_task中的状态改为待服务,同时将造型师信息加入表中
        $stylistData['salonId'] = $stylistInfo['salonId'];
        $stylistData['btStatus'] = 2;
        $stylistData['requestNum'] = 1;
        $stylistData['addTime'] = time();
        $stylistData['selectTime'] = time();
        $stylistData['btSn'] = $bountySn;
        $stylistData['hairstylistId'] = $stylistId;
        /*
        $res1 = M('bounty_task')->where(array('btSn' => $bountySn))->save($stylistData);
         */
        $res1 = D('Bounty')->updateStylistSelectedBySn($stylistData);
        if($res1 === false){
            $this->error(2516);
        }
        //更新bounty_request
        /*
        $requestData['btSn'] = $bountySn;
        $requestData['salonId'] = $salonId;
        $requestData['hairstylistId'] = $stylistId;
        $requestData['brStatus'] = 2;
        $requestData['addTime'] = time();
        $res2 = M('bounty_request')->add($requestData);
         */
        $res2 = D('BountyRequest')->addBountyRequest($bountySn, $salonId, $stylistId, 2, time());
        if($res2 === false){
            $this->error(2516);
        }
        
        //添加数据到push表，如果臭美代选，只需要将选中的用户写入push表。
        /*
        $pushSingleInfo = array(
            'userId' => $userId,
            'btSn' => $bountySn,
            'status' => 2,
            'reqStatus' => 3,   //用户已选中
            'stylistId' => $stylistId,
            'ostype' => $osType,
            'addTime' => time(),
        );
        $resPush = M('bounty_push')->add($pushSingleInfo);
         */
        $resPush = D('BountyPush')->addBountyPush($userId, $bountySn, 2, 3, $stylistId, $osType, time());
        if(!$resPush){
            $this->error(2601);
        }       
        //将消息推送给此人
        $phoneType = $stylistInfo['osType'];
        $type = 2;
        $stylistIdList = array(
            'android' => array($stylistId),
            'ios' => array($stylistId),
        );
        $info = array(
            'bountySn' => $bountySn,
            'stylistIdList' => $stylistIdList,
        );
        $this->allTypeMessage($info,$type,$phoneType);
        $this->success(1);   
    }
    
    /**
     * 自己选
     */
    private function pushMoreMessage($taskInfo,$stylistInfo,$bountySn){
        
        $userId = $taskInfo['userId'];
        $userName = $taskInfo['name']; 
        $money = $taskInfo['money'];
        // 推送消息给造型师客户端，同时存入推送数据到数据库
        $stylistIdList = $this->iOSAndAndroidStylist($stylistInfo);
        $info = array(
            'userId' => $userId,
            'bountySn' => $bountySn,
            'stylistIdList' => $stylistIdList,
            'name' => $userName,
            'money' => $money
        );
        if(!empty($stylistIdList['android'])){
            $this->pushAndroidMessage($info,1);
        }
        if(!empty($stylistIdList['ios'])){
            $this->pushIOSMessage($info,1);
        }
        //将others中的数据写入到日志中
        $others = $stylistIdList['others'];
        if($others){
            foreach($others as $othersId){
                $otherMessage[] = array('stylistId' =>$othersId);
            }
            $message = json_encode($otherMessage);
            Log::write("Stylists who do not recieve Push Messsage:".$message);
        }
        $this->success(1);
    }
    /**
     * android 消息推送，存入数据库
     * $info 推送用户id ， $type 推送类型，5种
     */
    private function pushAndroidMessage($info,$type=1){
        \Think\Log::record("pushAndroidMessage");
        $androidStylist = array();
        //返回造型师加密后的id        
        $androidStylist = $this->encryptStylist($info['stylistIdList']['android']);              
        //参数
        $payLoad = array(
            'msgType' => 1,
            'bountySn' => $info['bountySn']
        );
        $payLoad = json_encode($payLoad);
        
        \Think\Log::record("pushAndroidMessage payLoad:".print_r($payLoad, true));
        \Think\Log::record("pushAndroidMessage type:".print_r($type, true));
        if($type == 1){           
            $data['title'] = "新任务"; //选填,消息标题(如果passThrough设置为0,则标题为必填).
            $data['desc'] = "新鲜出炉的".$info['money']."元赏金任务，抢抢抢！";  //选填,消息内容(如果passThrough设置为0,则内容为必填).
        }else if($type == 2){
            $data['title'] = "悬赏通知"; 
            $data['desc'] = "你已被任务发布者选中，快去联系TA吧！";  
        }else if($type == 3){
            $data['title'] = "打赏消息";
            $data['desc'] = "恭喜，你有一单任务获得了全额打赏！"; 
        }else if($type == 4){
            $data['title'] = "打赏消息"; 
            $data['desc'] = "好遗憾，你收到了一份不满意的打赏"; 
        }else if($type == 5){
            $data['title'] = "新消息"; 
            $data['desc'] = "你有一单任务已被发布者取消！";  
        }else{
            $this->error(2605);
        }
        $data['payload'] = $payLoad; //选填,携带的数据,点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        $data['passThrough'] ="0"; //选填,是否需要透传,如果需要透传,把这个参数设置成1,同时去掉title和descption两个参数.
        $data['notifyForeground'] = "1";//选填,应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0.
        $data['notifyId'] = "0"; // 选填,通知类型.最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存.
//        $data['targetType'] = "alias"; //选填,push方式.alias,topic,regId,all四种方式.
//        $data['targetList'] = implode(",", $androidStylist); //选填,如果targetType不是all,则必填,以逗号分隔的字符串.
        $data['app'] = "v2";   //来源，造型师app推送
        $data["appType"]="android";
        
        //增加请求时写入日志
        $firstMessage = array('data' => $data,'type' => $type,'time' => time());
        $logmessage = json_encode($firstMessage);
        Log::write("push android message:".$logmessage);
        
//        $url = C('PUSH_MESSAGE_ANDROID_URL');
//        $postRes = curlPost($url,$data);
        \Think\Log::record("pushAndroidMessage data:".print_r($data, true));
        \Think\Log::record("pushAndroidMessage androidStylist:".print_r($androidStylist, true));
        $postRes = D("Push")->sendToAliases($data,$androidStylist);
        $postRes = json_decode($postRes,true);
        /*
        if($postRes['result'] && ($type == 1 || $type == 2) ){   
                       
            //更新消息推送状态为status =1
            if($type == 1){
               $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => array('in',$info['stylistIdList']['android'])
                ); 
            }
            if($type ==2 ){
                $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => $info['stylistIdList']['android'][0]
                );
            }
            $updateRes = M('bounty_push')->where($updateWhere)->save(array('status' => 1));
            if(!$updateRes){
                $this->error(2603);
            }            
        }else if(!$postRes['result'] && ($type == 1 || $type == 2)){
            //更新消息推送状态为status =0
            if($type == 1){
               $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => array('in',$info['stylistIdList']['android'])
                ); 
            }
            if($type ==2 ){
                $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => $info['stylistIdList']['android'][0]
                );
            }
            $updateRes = M('bounty_push')->where($updateWhere)->save(array('status' => 0));
            if(!$updateRes){
                $this->error(2604);
            }          
        }else{
            $otherMessage = array('btSn' => $info['bountySn'],'type' => $type,'stylistId' => $info['stylistIdList']['android'][0]);
            $message = json_encode($otherMessage);
            Log::write("other type message:".$message);
        }
        */
        $otherMessage = array('btSn' => $info['bountySn'],'type' => $type,'stylistId' => $info['stylistIdList']['android'][0],'postRes' => $postRes);
        $message = json_encode($otherMessage);
        Log::write("return android message:".$message);
    }
    /**
     * iOS 消息推送，存入数据库
     */
    private function pushIOSMessage($info,$type =1){
        $iOSStylist = array();
        //返回造型师加密后的id
        $iOSStylist = $this->encryptStylist($info['stylistIdList']['ios']);
        $payLoad = array(
            'msgType' => 1,
            'bountySn' => $info['bountySn']
        );
        $payLoad = json_encode($payLoad);
        \Think\Log::record("pushIOSMessage payLoad:".print_r($payLoad, true));
        \Think\Log::record("pushIOSMessage type:".print_r($type, true));
        //参数
        if($type == 1){           
            
            $data['desc'] = "新鲜出炉的".$info['money']."元赏金任务，抢抢抢！";  //选填,消息内容(如果passThrough设置为0,则内容为必填).
        }else if($type == 2){
            
            $data['desc'] = "你已被任务发布者选中，快去联系TA吧！";  
        }else if($type == 3){
            
            $data['desc'] = "恭喜，你有一单任务获得了全额打赏！"; 
        }else if($type == 4){
            
            $data['desc'] = "好遗憾，你收到了一份不满意的打赏"; 
        }else if($type == 5){
           
            $data['desc'] = "你有一单任务已被发布者取消！";  
        }else{
            $this->error(2605);
        }
        $data['payload'] = $payLoad; //选填,携带的数据,点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        //$data['soundUrl'] = ""; //选填，提示音
//        $data['targetType'] = "alias"; //选填,push方式.alias,topic,regId,all四种方式.
//        $data['targetList'] = implode(",", $iOSStylist); //选填,如果targetType不是all,则必填,以逗号分隔的字符串.
        $data['app'] = "v2";   //来源，造型师app推送
        $data['appType']= "ios";
        
        //增加请求时写入日志
        $firstMessage = array('data' => $data,'type' => $type,'time' => time());
        $logmessage = json_encode($firstMessage);
        Log::write("push ios message:".$logmessage);
//        $url = C('PUSH_MESSAGE_IOS_URL');
//        $postRes = curlPost($url,$data);
        \Think\Log::record("pushIOSMessage data:".print_r($data, true));
        \Think\Log::record("pushIOSMessage iOSStylist:".print_r($iOSStylist, true));
        $postRes = D("Push")->sendToAliases($data,$iOSStylist); 
        $postRes = json_decode($postRes,true); 
        /*
        if($postRes['result'] && ($type == 1 || $type == 2)){ 
            //更新消息推送状态为status =1
            if($type == 1){
               $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => array('in',$info['stylistIdList']['ios'])
                ); 
            }
            if($type ==2 ){
                $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => $info['stylistIdList']['ios'][0]
                );
            }
            $updateRes = M('bounty_push')->where($updateWhere)->save(array('status' => 1));
            if(!$updateRes){
                $this->error(2603);
            }    
            
        }else if(!$postRes['result'] && ($type == 1 || $type == 2)){
            //更新消息推送状态为status =0
            if($type == 1){
               $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => array('in',$info['stylistIdList']['ios'])
                ); 
            }
            if($type ==2 ){
                $updateWhere = array(
                    'btSn' => $info['bountySn'],
                    'stylistId' => $info['stylistIdList']['ios'][0]
                );
            }
            $updateRes = M('bounty_push')->where($updateWhere)->save(array('status' => 0));
            if(!$updateRes){
                $this->error(2604);
            }     
        }else{
            $otherMessage = array('btSn' => $info['bountySn'],'type' => $type,'stylistId' => $info['stylistIdList']['ios'][0]);
            $message = json_encode($otherMessage);
            Log::write("other type message:".$message);
        }
         */
        $otherMessage = array('btSn' => $info['bountySn'],'type' => $type,'stylistId' => $info['stylistIdList']['ios'][0],'postRes' => $postRes);
        $message = json_encode($otherMessage);
        Log::write("return ios message:".$message);
       
    }
    //获取安卓和iOS用户,区分开来
    public function iOSAndAndroidStylist($stylistInfo){
        $res = array();
        foreach ($stylistInfo as $value) {
            if($value['osType'] == 1){
                $res['android'][] = $value['stylistId'];
            }else if($value['osType'] == 2){
                $res['ios'][] = $value['stylistId'];
            }else{
                $res['others'][] = $value['stylistId'];
            }                   
        }
        return $res;
    }
    /**
     * 将造型师id加密用于设置推送
     */
    private function encryptStylist($stylist = array()){
        foreach ($stylist as $value) {
            $res[] = D('Des')->encrypt($value);
        }
        return $res;
    }
    /**
     * 发布闺蜜团任务
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
        $data['name'] = $paramData['name'];
        $data['district'] = intval($paramData['district']);
        $data['zone'] = intval($paramData['zone']);
        $data['selectType'] = intval($paramData['selectType']);
        $data['detail'] = json_encode($paramData['detail'], JSON_UNESCAPED_UNICODE);
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
            /**
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
    /*
     * 发布消息展示
     * @author huliang
     */
    public function bountyMessage(){
        $startTime = strtotime(date('Y-m-d'));
        $endTime = strtotime(date('Y-m-d',strtotime('+1 day')));
        //echo $startTime."</br>".$endTime;exit;
        //时间是当天内 且支付了
        /*
        $where = array(
            'addTime' => array(array('egt',$startTime),array('elt',$endTime)),
            'isPay' => 2
        );
        $res = M('bounty_task')->where($where)->order('addTime desc')->limit(10)->select();
         */
        $res = D('Bounty')->getBountyTaskList($startTime,$endTime,2);
        if(empty($res)){
            $this->error(2602);
        }
        $message = array();
        foreach ($res as $value) {
            $data['name'] = $value['name'];
            $data['time'] = times($value['addTime']);
            $data['money'] = intval($value['money']);
            $level = D('UserLevel')->getLevelByUid($value['userId']);
            $data['level'] = $level;
            $message[] = $data;
        }
        $this->ret["main"] = $message;
        $this->success();
        
    }
    /**
     * 多类型推送消息
     */
    private function allTypeMessage($info,$type,$phoneType){
        if($phoneType == 1){
            $this->pushAndroidMessage($info,$type);
        }else if($phoneType == 2){
            $this->pushIOSMessage($info,$type);
        }else{
            $otherMessage = array('info' => $info,'type' => $type,'phoneType' => $phoneType);
            $message = json_encode($otherMessage);
            Log::write("Stylist phoneType is not correct:".$message);
        }      
    }
}