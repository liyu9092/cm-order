<?php
/**
 * 规格模板-价格 视图
 * @author carson
 */
namespace Home\Model;
use Think\Model\ViewModel;

class SalonNormsPriceViewModel extends ViewModel {
	
	public $viewFields = array(
        'salon_norms' => array('salon_item_format_id','salon_norms_id','_type'=>'LEFT'),
		'salon_item_format_price' => array('salon_item_format_price_id','price','price_dis','price_group','_on' => 'salon_norms.salon_norms_id=salon_item_format_price.salon_norms_id')
	);

    public function getItemPriceFormats($itemId)
    {
        $thrift = D('ThriftHelper');
        $priceFormats = $thrift->request('seller-center', 'getItemPriceFormats', array($itemId));
        if(empty($priceFormats))
            return null;
        return $priceFormats;
    }
}
