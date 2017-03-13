<?php
	/****
	 * @author lufangrui
	 * @desc 获取店铺名称 这一块代码先做调试使用 等到内部商量好以后怎么调用内部产品接口 最终由产品接口来实现
	 * @time 2015-04-14
	 ****/

	namespace Home\Model;
	use Think\Model;

	class TownModel extends BaseModel {
		/***
		* 获取城市区域块名
		* @param $district 区域id
		* @param $iid 城市id 默认取深圳地区
		* @lufangrui
		* @2015-05-16
		***/
		public function getTown( $district , $iid = 1 ){
            
            $thrift = D('ThriftHelper');
            $info = $thrift->request('seller-center', 'getLocationInfo', array(3, $district));
            if(empty($info))
                return null;
            return $info['name'];
            
            /*
			$townModel = D('town');
			$fields = array( 'tname' );
			$where = ' tid = %d and iid = %d ';
			$condition = array( $district , $iid );
			$return = $townModel->field( $fields )->where( $where,$condition )->find();
			return empty($return) ? '' : $return['tname'] ;
             */
		}
	}
?>
