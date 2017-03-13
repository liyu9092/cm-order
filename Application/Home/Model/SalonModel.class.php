<?php

/****
 * @author lufangrui
 * @desc 获取店铺名称 这一块代码先做调试使用 等到内部商量好以后怎么调用内部产品接口 最终由产品接口来实现
 * @time 2015-04-14
 ****/

namespace Home\Model;
use Think\Model;

class SalonModel extends BaseModel {
    
    private $EARTH_RADIUS = 6371.393;//地球半径

    private function rad($d){
        return $d * M_PI/180.0;
    }

    /**
     * 通过经纬度计算距离
     * @param $lat1     纬度
     * @param $lng1     经度
     * @param $lat2
     * @param $lng2
     * @return float
     */
    public function getDistance($lat1,$lng1,$lat2,$lng2){
        $radLat1 = $this->rad($lat1);
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);

        $s = 2 * asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s * $this->EARTH_RADIUS;
        $s = round($s * 1000000) / 1000000;
        //echo $s;
        return $s;
    }
    /****
     * 根据店铺id获取店铺名称
     ****/
    public function getSalonNameById ( $salonId ){

        $salon = $this->getSalonById($salonId);
        return $salon['salonname'];
        /*
        $where = " salonid = %d ";
        $condition = array( $salonId );
        $fields = array( "salonname" );
        $return = $this->field( $fields )->where( $where , $condition )->find();
        return $return;
         */
    }
    /****
     * 根据店铺id获取店铺大概信息
     ****/
    public function getSimplyInfoById ( $salonId , $addrLati , $addrLong ){
        
        $salon = $this->getSalonById($salonId);
        $ret = array(
            "salonname" => $salon['salonname'], 
            "commentnum" => $salon['commentnum'],
            "goodScale" => ($salon['commentnum']-$salon['satisfyThree'])/$salon['commentnum'],
            "salonid" => $salon['salonid'], 
            "addr" => $salon['addr'],
            "logo" => $salon['logo'],
            "addrlati" => $salon['addrlati'],
            "addrlong" => $salon['addrlong'],
        );
        if( !empty( $addrLati ) && !empty($addrLong))
        {
            $ret['dist'] = $this->getDistance($addrLati, $addrLong, $salon['addrlati'], $salon['addrlong'])*1000;
        }
            /*
            $ret['dist'] = sqrt(pow($addrLati-$salon['addrlati'], 2)+pow($addrLong-$salon['addrlong'], 2))*111000;
             */
        return $ret;
        /*
        $where = " salonid = %d ";
        $condition = array( $salonId );
        $fields = array( "salonname" , "commentnum" , "(commentnum-satisfyThree)/commentnum as goodScale" , "salonid" , "addr" ,"logo","addrlati","addrlong");

        if( !empty( $addrLati ) && !empty($addrLong))
            $fields[] = "sqrt(({$addrLati}-addrlati)*({$addrLati}-addrlati)+({$addrLong}-addrlong)*({$addrLong}-addrlong))*111000 dist";

        $return = $this->field( $fields )->where( $where , $condition )->find();
        return $return;
         */
    }
    /***
     * 根据店铺的区域和商圈号获取对应的值
     */
    public function getTownAndAreaName($townId,$areaId){
        /*
        $res['townName'] = M('town')->where('tid = %d',$townId)->getField('tname');
        $res['areaName'] = M('salon_area')->where('areaid = %d',$areaId)->getField('areaname');
         */
        $res['townName'] = D('Town')->getTown($townId);
        $res['areaName'] = D('SalonArea')->getAreaName($areaId);
        return $res;
    }
	/***
	* 获取店铺名字
	* @param $salonId 店铺id
	* @lufangrui
	* @2015-05-16
	***/
	public function getSalon( $salonId ){
        $salon = $this->getSalonById($salonId);
        return array('salonname' => $salon['salonname']);
        /*
		$field = array( 'salonname' );
		$where = ' salonid = %d';
		$condition = array( $salonId );
		$return = $this->field( $fields )->where( $where,$condition )->find();
		return $return;
         */
	}
    
    public function getSalonById($salonId)
    {
        $thrift = D('ThriftHelper');
        $salon = $thrift->request('seller-center', 'getSalonInfo', array($salonId));
        return $salon;
    }
    
    public function updateComment($salonId, $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep)
    {
        $thrift = D('ThriftHelper');
        $ret = $thrift->request('seller-center', 'updateSalonComment', array($salonId, $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep));
        return $ret;
    }
}