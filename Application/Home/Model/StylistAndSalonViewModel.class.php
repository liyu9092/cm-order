<?php
namespace Home\Model;
use Think\Model\ViewModel;

/**
 *
 */
class StylistAndSalonViewModel extends ViewModel {
	
//	public $viewFields = array(
//        'salon' => array('salonname','district','zone','bountyType','salestatus'),
//        'hairstylist' => array('stylistId','stylistName','stylistImg','grade','status' => 'stylistStatus','osType','_on' => 'salon.salonid=hairstylist.salonId')
//	);
	public function getStylistByGrade($salonCats,$stylistCats,$district,$zone)
        {
             $thrift = D('ThriftHelper');
             $return = $thrift->request('search-center', 'getStylistByGrade', array($salonCats,$stylistCats,$district,$zone));
             return $return;
        }  
}
