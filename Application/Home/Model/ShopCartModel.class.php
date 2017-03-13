<?php
/**
 * 订单处理类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class ShopCartModel extends BaseModel {
  
     //得到购物车数量
    public function getShopCartNums($userId){
//        $ShopcartObj=M('shopcart');
//        $firstWhere['userId']=$userId;
//        $shopcartTotal=$ShopcartObj->where($firstWhere)->sum('nums');
//        return $shopcartTotal;
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getShopCartNums', array($userId));
        return $return;
    }
    
    //获取购物车列表
    public function getShopCartList($userId){
//        $ShopcartObj=M('shopcart');
//        $where['userId']=$userId;
//        return $list=$ShopcartObj->where($where)->order('addTime desc')->select();
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getShopCartList', array($userId));
        return $return;
    }

    //根据$scIds获取购物车
    public function getShopCarts($scIds,$userId){
        
//        $ShopcartObj=M('shopcart');
//        $where['scId']=array('in',$scIds);
//        $where['userId']=$userId;
//        return $spList=$ShopcartObj->where($where)->select();
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getShopCarts', array($scIds,$userId));
        return $return;
    }
            
    //根据购物车号获取用户购物车
    public function getShopCartBySn($userId,$shopcartSn){
//        $ShopcartObj=M('shopcart');
//        $where['userId']=$userId;
//        $where['shopcartsn']=$shopcartSn;
//        return $list=$ShopcartObj->where($where)->select();
        /*
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getShopCartBySn', array($userId,$shopcartSn));
        return $return;
         */
        $items = $this->getAllShopCartBySn($shopcartSn);
        $ret = array();
        foreach($items as $item)
        {
            if($item['userId'] != $userId)
                continue;
            $ret[] = $item;
        }
        return $ret;
    }
    
    //根据购物车号获取所有购物车
    public function getAllShopCartBySn($shopcartSn){
//        $ShopcartObj=M('shopcart');
//        $where['shopcartsn']=$shopcartSn;
//        return $list=$ShopcartObj->where($where)->select();
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getAllShopCartBySn', array($shopcartSn));
        return $return;
    }
    
    //删除购物车列表
    public function deleteShopCartList($scId){
//        $ShopcartObj=M('shopcart');
//        return $ShopcartObj->where('scId='.$scId)->delete();
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'deleteShopCartList', array($scId));
        return $return;
    }
    

    //根据用户id删除对应scId的购物车列表
    public function deleteShopCarts($scIds,$userId){
        /*
        $ShopcartObj=M('shopcart');
        $where['scId']=array('in',$scIds);
        $where['userId']=$userId;

        return $rs=$ShopcartObj->where($where)->delete();
         */
      
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'deleteShopCarts', array($scIds,$userId));
        return $return; 
         
    }
    //根据购物车号删除购物车列表
    public function deleteShopCartBySn($shopcartSn,$userId){        
//        $spWhere['userId']=$userId;
//        $spWhere['shopcartsn']=$shopcartsn;
//        return $spRs=M('shopcart')->where($spWhere)->delete(); 
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'deleteShopCartBySn', array($shopcartSn,$userId));
        return $return;
    }
    
    //根据项目和规格获取用户购物车
    public function getShopCartByItemNorms($itemId,$salonNormsId, $userId){
        $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'getShopCartByItemNorms', array($itemId,$salonNormsId,$userId));
        return $return;
    }
    
    //根据规格更新购物车(条件为itemId, salonNormsId, userId)
    public function updateShopCartByNorms($itemId, $salonNormsId, $userId,$nums,$priceAll,$priceDisAll){
        $thrift = D('ThriftHelper');
        $param = new \cn\choumei\thriftserver\service\stub\gen\UpdateShopCartParam();
        $param->itemId = $itemId;
        $param->nums = $nums;
        $param->priceAll = $priceAll;
        $param->priceDisAll = $priceDisAll;
        $param->salonNormsId = $salonNormsId;
        $param->userId = $userId;
        $return = $thrift->request('trade-center', 'updateShopCartByNorms', array($param));
        /*
        $return = $thrift->request('trade-center', 'updateShopCartByNorms', array($itemId, $salonNormsId, $userId,$nums,$priceAll,$priceDisAll));
         */
        return $return;
    }
    
    //根据id更新购物车(条件为scId, userId)
   public function updatShopCartById($scId, $userId, $nums,$datapriceAll,$datapriceDisAll){
       
       $thrift = D('ThriftHelper');
        $return = $thrift->request('trade-center', 'updatShopCartById', array($scId, $userId, $nums,$datapriceAll,$datapriceDisAll));
        return $return;
   }
   
    //添加购物车
    public function addShopCart($salonId,$salonName,$itemId,$itemName,$salonNormsId,$salonNormsName,$nums,$price,$priceAll,$priceDis,$priceDisAll,$userId,$addTime){
          
        $thrift = D('ThriftHelper');
        
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddShopCartParam();
        $param->addTime = $addTime;
        $param->itemId = $itemId;
        $param->itemName = $itemName;
        $param->nums = $nums;
        $param->price = $price;
        $param->priceAll = $priceAll;
        $param->priceDis = $priceDis;
        $param->priceDisAll = $priceDisAll;
        $param->salonId = $salonId;
        $param->salonName = $salonName;
        $param->salonNormsId = $salonNormsId;
        $param->salonNormsName = $salonNormsName;
        $param->userId = $userId;
        $return = $thrift->request('trade-center', 'addShopCart', array($param));
        /*
        $return = $thrift->request('trade-center', 'addShopCart', array($salonId,$salonName,$itemId,$itemName,$salonNormsId,$salonNormsName,$nums,$price,$priceAll,$priceDis,$priceDisAll,$userId,$addTime));
         */
        return $return;
    }
            
    public function setShopCartSn($scIds,$userId,$shopcartsn) {
        
        $thrift = D('ThriftHelper');
        $ret = $thrift->request("trade-center", "setShopCartSn", array($scIds,$userId, $shopcartsn));       
        return $ret;      
    } 
       
        
}
