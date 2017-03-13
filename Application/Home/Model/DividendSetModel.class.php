<?php
/**
 * 分红联盟
 * @author carson
 */
namespace Home\Model;

class DividendSetModel extends BaseModel {

    public function getInfo()
    {
        $thrift = D('ThriftHelper');
        $dividendSet = $thrift->request('seller-center', 'getDividendSet', array());
        return $dividendSet;
    }
}