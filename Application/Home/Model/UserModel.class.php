<?php
/**
 * 用户
 * @author carson
 */
namespace Home\Model;

class UserModel extends BaseModel {
    private static $userInfo;
    private static $companyInfo;
    private static $userInfos;
    private static $companyInfos;
    

    /**
     * 获取用户信息
     * @param $id
     * @param string $field
     * @return array|mixed
     */
    public function getUserById($id){
        if(!self::$userInfos[$id]){
            $thrift = D('ThriftHelper');
            $userInfo = $thrift->request('user-center', 'getUserInfoByUserId', array($id));
            if($userInfo == false)
                return null;
            $userInfo['user_id'] = $userInfo['userId'];
            $userInfo['osType'] = $userInfo['osType'];
            $userInfo['hair_type'] = $userInfo['hairStyle'];
            self::$userInfos[$id] = $userInfo;
        }
        return self::$userInfos[$id];
        
        //原版代码有bug,同一进程内，使用两个不同uid调用，结果一致
        /*
        if(!self::$userInfo){
            self::$userInfo=$this->getInfoById($id,$field);
        }
        return self::$userInfo;
        */
    }


    /**
     * 获取用户集团邀请码的状态信息
     * @param $companyId
     * @return mixed
     */
    public function checkCompanyRs($companyId){
        /*
        $data['companyStatus']=0;
        if($companyId){
            $where['companyId']=$companyId;
            $where['status']=1;

            if(!self::$companyInfo){
                self::$companyInfo=M('company_code')->field('companyId,companyAcronym')->where($where)->find();
            }
            if(self::$companyInfo){
                $data['companyStatus']=1;
                $data['companyId']=self::$companyInfo['companyId'];
                $data['companyName']=self::$companyInfo['companyAcronym'];
                $data['companyIcon']=self::$companyInfo['imgUrl'];
            }
        }
        return $data;
         */
        
        $data['companyStatus']=0;
        if($companyId){
            $where['companyId']=$companyId;
            $where['status']=1;

            if(!self::$companyInfos[$companyId]){
                
                self::$companyInfos[$companyId] = D('CompanyCode')->getCompanyById($companyId);
                if(self::$companyInfos[$companyId]['status'] != 1)
                    self::$companyInfos[$companyId] = null;
            }
            if(self::$companyInfos[$companyId]){
                $data['companyStatus']=1;
                $data['companyId']=self::$companyInfos[$companyId]['companyId'];
                $data['companyName']=self::$companyInfos[$companyId]['companyAcronym'];
                $data['companyIcon']=self::$companyInfos[$companyId]['companyIcon'];
            }
        }
        return $data;
    }


    /**
     * 根据用户ID获取用户集团邀请码的状态信息
     * @param $userId
     */
    public function getUserCompanyStatus($userId){
        $userInfo=$this->getUserById($userId);

        return $this->checkCompanyRs($userInfo['companyId']);
    }
    
    /**
     * 更新用户的成长值
     */
    public function updateUserGrowth($userId,$point) {
        /*
        M('user')->where("user_id = $userId")->setInc('growth',$point);
         */
        $thrift = D('ThriftHelper');
        $return = $thrift->request('user-center', 'updateUserGrowth', array($userId,$point));
        return $return;
    }
    
    /**
     * 扣/添用户账户余额
     * @param type $userId
     * @param type $money 负数为扣除，正数为添加
     * @return type
     */
    public function updateUserMoney($userId, $money)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('user-center', 'updateUserMoney', array($userId, $money));
    }


    /**
     * 通过用户id获取集团信息
     * @param $userId
     * @return bool
     */
    public function getCompanyInfoByUserId($userId){
        $userInfo=$this->getUserById($userId);
        if(!$userInfo || !$userInfo['companyId']){
            return false;
        }
        $companyInfo=D('CompanyCode')->getCompanyById($userInfo['companyId']);
        if(!$companyInfo){
            return false;
        }

        return $companyInfo;
    }

    /**
     * 通过用户id获取集团码
     * @param $userId
     * @return bool
     */
    public function getUseCompanyCodeByUserId($userId){

        $companyInfo=$this->getCompanyInfoByUserId($userId);
        if(!$companyInfo || !$companyInfo['code']){
            return false;
        }

        return $companyInfo['code'];
    }
}
