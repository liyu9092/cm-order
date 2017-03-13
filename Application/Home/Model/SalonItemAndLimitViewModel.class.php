<?php
/**
 * 项目分类-项目限制 视图
 * @author carson
 */
namespace Home\Model;
use Think\Model\ViewModel;

class SalonItemAndLimitViewModel extends ViewModel {
	
	public $viewFields = array(
        'salon_item' => array('total_rep','sold','useLimit','item_type','_type'=>'LEFT'),
		'salon_item_buylimit' => array('limit_time','limit_invite','limit_first','_on' => 'salon_item.itemid=salon_item_buylimit.salon_item_id')
	);


    public function getItemlimitInfoMark($itemId){
        if(!$itemId){
            return '';
        }
        /*
        $info=$this->where('salon_item.itemid='.$itemId)->find();
        //print_r($this->_sql());
        if(!$info){
            return '';
        }
         */
        $info = $this->getInfoByItemId($itemId);
        $item = D('SalonItem')->getItemById($itemId);
        if(!$item){
            return '';
        }
        $info['total_rep'] = $item['totalRep'];
        $info['sold'] = $item['sold'];
        $info['useLimit'] = $item['useLimit'];
        $info['item_type'] = $item['itemType'];
        
        
        $limitInfo=array();
        if($info['limit_first']){
            $limitInfo[]='仅首单用户可购';
        }
        if($info['limit_invite']){
            $limitInfo[]='输入本店邀请码可购';
        }
        if($info['limit_time']){
            $limitInfo[]='单个用户限购'.$info['limit_time'].'件';
        }
        if($info['total_rep']){
            $dif=$info['total_rep']-$info['sold'];
            if($dif<1){
                $dif=0;
            }
            $limitInfo[]='库存仅剩'.$dif;
        }
        return array($limitInfo,$info['useLimit']);
    }
    
    public function getInfoByItemId( $itemId ){
        $thrift = D('ThriftHelper');
        $buylimit = $thrift->request('seller-center', 'getItemBuyLimit', array($itemId));
        if(empty($buylimit))
            return null;
        return array("limit_time" => $buylimit['limitTime'], "limit_invite" => $buylimit['limitInvite'], "limit_first" => $buylimit['limitFirst']);
    }
}
