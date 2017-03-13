<?php
namespace Home\Model;
use Think\Model;

/**
 * 会员等级
 * @author carson
 */
class UserLevelModel extends BaseModel {

    /**
     * 通过成长值来获取等级
     * @param $growth
     */
    public function getLevelByGrowth($growth) {
        $sKey='userLevel';  //缓存key
      //  $sTime=604800;      //时间 一周
	      $sTime=10;  //时间 10s
        if(!$growth){
            return 0;
        }

        $levelArr=S($sKey); //取出缓存
        if(!$levelArr){
            /*
            $levelList=M('user_level')->order('level')->select();
             */
            $levelList = $this->getLevelInfo();
            if($levelList){
                $levelArr=array();
                foreach($levelList as $listV){
                    $levelArr[$listV['growth']]=$listV['level'];
                }

                S($sKey,$levelArr,array('type'=>'file','expire'=>$sTime));//存入缓存
            }else{
                return 0;
            }

        }
        return $this->calculateLevel($growth,$levelArr);
    }
    
    private function getLevelInfo()
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('user-center', 'getUserLevelRule', array());
    }


    /**
     * 通过userid获取等级
     * @param $userid
     * @return bool|void
     */
    public function getLevelByUid($userid) {
        if(!$userid){
            return false;
        }
        /*
        $userInfo=M('user')->where('user_id='.$userid)->find();
         */
        
        $userInfo = D('User')->getUserById($userid);
        if(!$userInfo){
            return false;
        }
        return $this->getLevelByGrowth($userInfo['growth']);
    }


    /**
     * 计算出等级
     * @param $growth
     * @param $levelArr
     * @return int
     */
    public function calculateLevel($growth,$levelArr){
        $level=0;

        $keys=array_keys($levelArr);
        $keyTemp=array();
        foreach($keys as $kV){
            if($kV<=$growth){
                $keyTemp[]=$kV;
            }
        }
        $maxKey=max($keyTemp);
        if($maxKey){
            $level=$levelArr[$maxKey];
        }

        return $level;
    }

}
