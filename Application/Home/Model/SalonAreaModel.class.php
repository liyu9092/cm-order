<?php
	
	/****
	 * @author lufangrui 
	 * @desc   用于获取区域表处理
	 * @time 	2015-04-09
	 ****/
	namespace Home\Model;
	use Think\Model;
	class SalonAreaModel extends Model {
		/****
		 * 通过分店zone获取分店对应的商圈
		 ****/
		public function getAreaName( $zone ){
            $thrift = D('ThriftHelper');
            $info = $thrift->request('seller-center', 'getLocationInfo', array(4, $zone));
            if(empty($info))
                return false;
            return $info['name'];
            
            /*
			$field = array("areaname");
			$where = " areaid = %d ";
			$return = $this->field($field)->where( $where , array( $zone ) )->find();
// 			echo $this->getLastSql();
// 			echo "\r\n";
			if( empty($return) )
				return false;
			return $return["areaname"];
            */
		}
        
        /****
		 * 通过父Id获取商圈
		 ****/
        public function getAreaByParentId( $parentId ){
            $thrift = D('ThriftHelper');
            $info = $thrift->request('seller-center', 'getSubLocations', array(3, $parentId));
            return $info;
		}
	}
