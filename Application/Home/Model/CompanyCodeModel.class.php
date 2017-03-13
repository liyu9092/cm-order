<?php
/**
 * 订单处理类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class CompanyCodeModel extends BaseModel {
  
     //通过集团id找到集团名称
    public function getCompanyAcronym($companyId){
        $company = $this->getCompanyById($companyId);
        return $company['companyAcronym'];
        /*
        $companyAcronym = M('company_code')->where('companyId ='.$companyId)->getField('companyAcronym');
        return $companyAcronym;
         */
    }
    
    public function getCompanyById($companyId)
    {
        $thrift = D('ThriftHelper');
        $companyCode = $thrift->request('seller-center', 'getCompanyCodeById', array($companyId));
        return $companyCode; 
    }
    
}
