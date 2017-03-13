<?php
/**
 * 购物车处理类
 *
 * @author carson
 */
namespace Home\Controller;

class ShopcartController extends OrderOutController{
    private $shopcartMax=10;
    private $shopcartTotalMax=100;


    /**
     * 加入购物车
     */
    public function add(){
        $salonId=intval($this->param['salonId']);
        $itemId=intval($this->param['itemId']);
        $salonNormsId=intval($this->param['salonNormsId']);
        if(!$salonId || !$itemId){
            $this->error(1);
        }

        /*
        $salonInfo=M('salon')->field('salonid,salonname')->find($salonId);
         */
        $salonInfo = D('Salon')->getSalonById($salonId);
        
        if(!$salonInfo){
            $this->error(1);
        }
        /*
        $iwhere['salonid']=$salonId;
        $iwhere['itemid']=$itemId;
        $iwhere['status']=1;
        $itemInfo=D('SalonItem')->getInfo($iwhere);
         */
        
        $item=D('SalonItem')->getItemById($itemId);
        if(!$item || $item['salonid'] != $salonId)
            $this->error(1);
        if($item['status'] != 1)
            $this->error(2003);
        $itemInfo = array(
            'typeid' => $item['typeid'],
            'itemid' => $item['itemid'],
            'salonid' => $item['salonid'],
            'itemname' => $item['itemname'],
            'exp_time' => $item['expTime'],
            'total_rep' => $item['totalRep'],
            'sold' => $item['sold'],
            'desc' => $item['desc'],
            'useLimit' => $item['useLimit'],
            'item_type' => $item['itemType'],
            'innage' => $item['innage'],
            'repertory' => $item['repertory'],
        );    
        if(!$itemInfo){
            $this->error(2003);
        }
        /*
        $ShopcartObj=M('shopcart');

        $firstWhere['userId']=$this->userId;
        $shopcartTotal=$ShopcartObj->where($firstWhere)->sum('nums');
         */
        $ShopcartObj=D('ShopCart');
        $shopcartTotal= $ShopcartObj->getShopCartNums($this->userId);
        if(($shopcartTotal+1)>$this->shopcartTotalMax){
            $this->error(2926);
        }

        $priceInfo=D('Order')->getOrderPrice($this->userId,$itemId,$salonNormsId);
        if(!$priceInfo){
            $this->error(2001);
        }
        $price = $priceInfo['price'];           //原价
        $priceDis = $priceInfo['price_dis'];    //现价



        $where['salonId']=$salonId;
        $where['itemId']=$itemId;
        $where['salonNormsId']=$salonNormsId;
        $where['userId']=$this->userId;
        /*
        $oldShop=$ShopcartObj->where($where)->find();
         */
        $oldShop=$ShopcartObj -> getShopCartByItemNorms($itemId,$salonNormsId, $this->userId);   
        
        
        $cartNums=1;
        if($oldShop){
            $cartNums=$oldShop['nums']+1;
            if($cartNums>$this->shopcartMax){
                $this->error(2924);
            }
        }

        $OrderObj=D('Order');
        //验证项目的信息
        $itemCkRs=$OrderObj->valiItemInfo($itemInfo,$cartNums,$this->userId);
        if($itemCkRs){
            $itemCkRs=$itemCkRs==2902?2925:$itemCkRs;
            $this->error($itemCkRs);
        }

        if($oldShop){
            if($oldShop['nums']>=$this->shopcartMax){
                $this->error(2922);
            }

            $data['nums']=$cartNums;
            $data['priceAll']=$data['nums']*$price;
            $data['priceDisAll']=$data['nums']*$priceDis;
            
            /*
            $rs=$ShopcartObj->where($where)->save($data);
             */
            /*
            $rs=$ShopcartObj->updateShopCartByNorms($itemId, $salonNormsId,$this->userId,$data['nums'],$data['priceAll'],$data['priceDisAll']);
             */
            $rs = D('ShopCart')->updatShopCartById($oldShop['scId'], $this->userId, $data['nums'], $data['priceAll'], $data['priceDisAll']);
        }else{
            $data['salonId']=$salonId;
            $data['salonName']=$salonInfo['salonname'];
            $data['itemId']=$itemId;
            $data['itemName']=$itemInfo['itemname'];
            $data['salonNormsId']=$salonNormsId;
            $data['salonNormsName']=$priceInfo['normsStr'];
            $data['nums']=$cartNums;
            $data['price']=$price;
            $data['priceAll']=$price;
            $data['priceDis']=$priceDis;
            $data['priceDisAll']=$priceDis;
            $data['userId']=$this->userId;
            $data['addTime']=time();
            
            /*
            $rs=$ShopcartObj->add($data);
             */
            $salonId=$salonId;
            $salonName=$salonInfo['salonname'];
            $itemId=$itemId;
            $itemName=$itemInfo['itemname'];
            $salonNormsId=$salonNormsId;
            $salonNormsName=$priceInfo['normsStr'];
            $nums=$cartNums;
            $price=$price;
            $priceAll=$price;
            $priceDis=$priceDis;
            $priceDisAll=$priceDis;
            $userId=$this->userId;
            $addTime=time();
            
            $rs=$ShopcartObj->addShopCart($salonId,$salonName,$itemId,$itemName,$salonNormsId,$salonNormsName,$nums,$price,$priceAll,$priceDis,$priceDisAll,$userId,$addTime);
        }
        if(!$rs){
            $this->error(2930);
        }

        $this->success();
    }


    /**
     * 购物车列表
     */
    public function shopcartList(){
        /*
        $ShopcartObj=M('shopcart');

        $where['userId']=$this->userId;
        $list=$ShopcartObj->where($where)->order('addTime desc')->select();
         */
        $ShopcartObj=D('ShopCart');
        $list = $ShopcartObj->getShopCartList($this->userId);
        if(!$list){
            $this->error(6);
        }
        //如果后台中将项目价格修改，此处在购物车列表中应该显示最新的价格，即cm_salon_item_format_price表中更新后的价格
        foreach ($list as &$listV){

            $priceInfo = D('Order')->getOrderPrice($listV['userId'],$listV['itemId'],$listV['salonNormsId']); //价格
            //如果找不到，就删除购物车中的该条记录
            if(!$priceInfo) {
                /*
                $res = $ShopcartObj->where('scId='.$listV['scId'])->delete();
                 */
                /*
                $res = $ShopcartObj->deleteShopcartList($listV['scId']);
                 */
                $res = $ShopcartObj->deleteShopCarts(array($listV['scId']), $this->userId);
                if(!$res){
                    $this->error(2927);
                }
            }else{
                $listV['priceDis'] = $priceInfo['price_dis'];
                $listV['priceDisAll'] = $priceInfo['price_dis'] * $listV['nums'];
            }       
        }
        list($lastArr,$totalPriceDis,$totalNums)=$this->assortShopcartList($list);

        $this->ret['main']=$lastArr;
        $this->ret['other']=array(
            'totalMoney'=>$totalPriceDis,
            'totalNums'=>$totalNums ?: 0
        );
        //print_r($ret);
        $this->success();
    }


    /**
     * 处理购物车列表的数据
     * @param $list
     * @return array
     */
    private function assortShopcartList(&$list){
        $SalonItemAndLimit=D('SalonItemAndLimitView');
        $one=array();
        foreach($list as &$listV){
            list($listV['saleRule'],$listV['useLimit'])=$SalonItemAndLimit->getItemlimitInfoMark($listV['itemId']);
            $one[$listV['salonId']][$listV['itemId']][]=$listV;
        }
        
        $lastArr=array();
        $totalPriceDis=0;   //总金额
        $totalNums=0;       //总数量
        //店铺处理
        foreach($one as $oneV){
            $salonName='';

            $theNums=0;
            $thePriceDis=0;

            $myOne=array();
            //项目处理
            foreach($oneV as $secV){

                //规格处理
                foreach($secV as $thrV){
                    $myTwo=array();

                    $salonId=$thrV['salonId'];
                    $salonName=$thrV['salonName'];
                    $itemId=$thrV['itemId'];
                    $itemName=$thrV['itemName'];


                    $theNums+=$thrV['nums'];                            //每店总数量
                    $thePriceDis+=$thrV['priceDis']*$thrV['nums'];      //每店总金额

                    $totalNums+=$thrV['nums'];                          //所有总数量
                    $totalPriceDis+=$thrV['priceDis']*$thrV['nums'];    //所有总金额

                    $myTwo['itemId']=$itemId;
                    $myTwo['itemName']=$itemName;
                    $myTwo['scId']=$thrV['scId'];
                    $myTwo['salonNormsId']=$thrV['salonNormsId'];
                    $myTwo['normsStr']=$thrV['salonNormsName'];
                    $myTwo['price']=$thrV['price'];
                    $myTwo['priceDis']=$thrV['priceDis'];
                    $myTwo['nums']=$thrV['nums'];
                    $myTwo['saleRule']=$thrV['saleRule'];
                    $myTwo['useLimit']=$thrV['useLimit'];


                    $myOne[]=$myTwo;
                }
            }
            $temp['salonId']=$salonId;
            $temp['salonName']=$salonName;
            $temp['items']=$myOne;
            $temp['nums']=$theNums;
            $temp['priceDis']=$thePriceDis;

            $lastArr[]=$temp;
        }

        return array($lastArr,$totalPriceDis,$totalNums);
    }


    /**
     * 购物车数量
     */
    public function shopcartNums(){
        /*
        $ShopcartObj=M('shopcart');

        $where['userId']=$this->userId;
        $list=$ShopcartObj->where($where)->sum('nums');
         */
        $ShopcartObj=D('ShopCart');
        $list=$ShopcartObj->getShopCartNums($this->userId);
        $list = !empty($list) ? $list:'';
        $this->ret['main']=array('nums'=>$list);
        //print_r($ret);
        $this->success();
    }



    /**
     * 数量编辑
     */
    public function upNum(){
        $scId=intval($this->param['scId']);
        $tp=intval($this->param['tp']);
        if(!$scId){
            $this->error(1);
        }
        /*
        $ShopCartObj=M('shopcart');
         */
        $where['scId']=$scId;
        $where['userId']=$this->userId;
        
        /*
        $cartInfo=$ShopCartObj->where($where)->find();
         */
        
        $cartInfo=D('ShopCart')->getShopCarts(array($scId),$this->userId);
        $cartInfo=$cartInfo[0];
        if(!$cartInfo){
            $this->error(2920);
        }

        if($tp==1){
            $this->param['salonId']=$cartInfo['salonId'];
            $this->param['itemId']=$cartInfo['itemId'];
            $this->param['salonNormsId']=$cartInfo['salonNormsId'];

            $this->add();
        }else{
            $data['nums']=$cartInfo['nums']-1;
            if($data['nums']<1){
                $this->error(2924);
            }
            $data['priceAll']=$cartInfo['price']*$data['nums'];
            $data['priceDisAll']=$cartInfo['priceDis']*$data['nums'];

            /*
            $rs=$ShopCartObj->where($where)->save($data);
             */
            $rs = D('ShopCart')->updatShopCartById($scId, $this->userId, $data['nums'], $data['priceAll'], $data['priceDisAll']);

            if(!$rs){
                $this->error(3);
            }

            $this->success();
        }
    }


    /**
     * 编辑购物车
     */
    public function deleteCart(){
        $scIds=$this->param['scIds'];
        if(!$scIds){
            $this->error(1);
        }
        $scIds=explode(',',$scIds);
        /*
        $where['scId']=array('in',$scIds);
        $where['userId']=$this->userId;

        $rs=M('shopcart')->where($where)->delete();
         */
        $rs = D('ShopCart')->deleteShopCarts($scIds,$this->userId);
        
        if(!$rs){
            $this->error(2);
        }

        $this->success();
    }


    /**
     * 提交订单
     */
    public function submitOrder(){
        $scIds=$this->param['scIds'];
        if(!$scIds){
            $this->error(1);
        }
        $scIds = rtrim(trim($scIds, ','), ',');
        $scIds=explode(',',$scIds);
        $where['scId']=array('in',$scIds);
        $where['userId']=$this->userId;

        /*
        $ShopCartObj=M('shopcart');
         */
        /*
        $spList=$ShopCartObj->where($where)->select();
         */
        $ShopCartObj = D('Shopcart');
        $spList=D('ShopCart')->getShopCarts($scIds,$this->userId);
        if(!$spList){
            $this->error(2920);
        }
        $shopcartsn=str_pad(time().$this->userId,20,'0');

        $ShopCartObj->startTrans();

        $OrderObj=D('Order');
        foreach($spList as $spListV){
            //print_r($spListV);
            //必须判断是否为负数 以防万一,不然订单生成就悲剧
            if($spListV['nums']<1){
                $ShopCartObj->rollback();
                $this->error(3);
            }
            
            if($spListV['nums']>1){
                for($i=1;$i<=$spListV['nums'];$i++){
                    $adRs=$OrderObj->shopcartAddOrder($this->userId,$shopcartsn,$spListV['itemId'],$spListV['salonNormsId'],$spListV['nums']);
                    if(!$adRs){
                        $ShopCartObj->rollback();

                        $pre='['.$spListV['salonName'].'-'.$spListV['itemName'].']';
                        $this->error($OrderObj->getError(),$pre);
                    }
                }
            }else{
                $adRs=$OrderObj->shopcartAddOrder($this->userId,$shopcartsn,$spListV['itemId'],$spListV['salonNormsId'],$spListV['nums']);
                if(!$adRs){
                    $ShopCartObj->rollback();

                    $pre='['.$spListV['salonName'].'-'.$spListV['itemName'].']';
                    $this->error($OrderObj->getError(),$pre);
                }
            }
        }
        /*
        $upShopRs=$ShopCartObj->where($where)->setField('shopcartsn',$shopcartsn);
         */
        $upShopRs=D("ShopCart")->setShopCartSn($scIds,$this->userId,$shopcartsn);
        if(!$upShopRs){
            $ShopCartObj->rollback();
            $this->error(3);
        }

        $ShopCartObj->commit();

        $this->ret['main']=array('shopcartsn'=>$shopcartsn);
        $this->success();
    }





    /**
     * 获取订单信息
     */
    public function requestOrderInfo(){
        $shopcartSn=$this->param['shopcartsn'];

        if(!$shopcartSn || !$this->userId){
            $this->error(1);
        }
        $userId = $this->userId;
        $voucherObj = D('Voucher');
        $allUniqueVoucherIds = $voucherObj->getVoucherIds($userId,$shopcartSn,2);
        //print_r($allUniqueVoucherIds);exit;
        if(empty($allUniqueVoucherIds[0])){
            $this->ret['other']['haveCanUsedVoucher'] = 0;
        }else{
            $this->ret['other']['haveCanUsedVoucher'] = 1;
            //上面去重之后，根据vId 的可使用金额从大到小排序
            /*
            $vIdDesc = M('voucher')->where(array('vId' => array('in',$allUniqueVoucherIds)))->order('vUseMoney desc')->select();  
            $allUniqueVoucherIds = array();
            foreach ($vIdDesc as $keyVid => $valueVid) {
                $allUniqueVoucherIds[] = $valueVid['vId'];
            }
             */
            $voucherMoney = $maxMoneyVid = 0;
            foreach($allUniqueVoucherIds as $uniqVId)
            {
                $voucher = D('Voucher')->getVoucherById($uniqVId);
                if($voucher['vUseMoney'] > $voucherMoney)
                {
                    $maxMoneyVid = $uniqVId;
                    $voucherMoney = $voucher['vUseMoney'];
                }
            }
            $allUniqueVoucherIds = array($maxMoneyVid);
            
        }     
        //print_r($allUniqueVoucherIds);exit;
        /*
        $ShopcartObj=M('shopcart');

        $where['userId']=$this->userId;
        $where['shopcartsn']=$shopcartSn;
        $list=$ShopcartObj->where($where)->select();
         */
        $ShopcartObj=D('ShopCart');
        $list = $ShopcartObj->getShopCartBySn($this->userId,$shopcartSn);
        if(!$list){
            $this->error(8);
        }
        
        //如果后台中将项目价格修改，此处在购物车详情中应该显示最新的价格，即cm_salon_item_format_price表中更新后的价格
        foreach ($list as &$listV){
            $priceInfo = D('Order')->getOrderPrice($listV['userId'],$listV['itemId'],$listV['salonNormsId']); //价格

            $listV['priceDis'] = $priceInfo['price_dis'];
            $listV['priceDisAll'] = $priceInfo['price_dis'] * $listV['nums'];
        }

        list($lastArr,$totalPriceDis,$totalNums)=$this->assortShopcartList($list);

        /*
        $UserObj = M('user');
        $userInfo = $UserObj->field('costpwd,money')->find($this->userId);
        */
        $UserObj = D('User');
        $userInfo=$UserObj->getUserById($this->userId);
        $this->ret['main']=$lastArr;
        $this->ret['other']['totalMoney'] = $totalPriceDis;
        $this->ret['other']['totalNums'] = $totalNums ?: 0;
        $this->ret['other']['balance'] = $userInfo['money'];
        $this->ret['other']['isenough'] = (($userInfo['money']-$totalPriceDis)>=0)?1:0;
        $this->ret['other']['paymoney'] = (($userInfo['money']-$totalPriceDis)>=0)?0:intval($totalPriceDis-$userInfo['money']);
        
        if($this->param['vCancelStatus'] == 1){                    
            //传入代金券vId
            if($this->param['vId']){
                $vId = intval($this->param['vId']);
                $voucherObj = D('Voucher');
                //根据购物车号获取订单号
                /*
                $allOrderSn = M('order')->field('ordersn,orderid')->where(array('shopcartsn' => $shopcartSn))->order('orderid')->select();
                 */
                $shopcartOrders = D('Order')->getOrderByShopcartSn($shopcartSn);
                foreach($shopcartOrders as $shopcartOrder)
                    $allOrderSn[] = array(
                        'ordersn' => $shopcartOrder['ordersn'],
                        'orderid' => $shopcartOrder['orderId'],
                        'priceall' => $shopcartOrder['priceall'],
                    );
                //print_r($allOrderSn);exit;
                $i = 0;
                foreach ($allOrderSn as $key => $value) {
                    //判断每一个订单，查询哪些订单可以使用此券
                    $vidCanBeUsedRes = $voucherObj->isVidCanBeUsed($vId,$userId,$value['ordersn']);
                    //如果可以使用，查询订单金额
                    if($vidCanBeUsedRes){
                        /*
                        $resOrder[$i]['price'] = M('order')->where(array('ordersn' => $value['ordersn']))->getField('priceall');
                         */
                        $resOrder[$i]['price'] = $value['priceall'];
                        $resOrder[$i]['orderSn'] = $value['ordersn'];
                        $resOrder[$i]['orderid'] = $value['orderid'];
                        $i++;
                    }
                }
                //为空则说明此券不可用
                foreach ($resOrder as $key3 => $value3) {
                    $isemptyArray[] = $value3['orderSn'];
                }
                //print_r($isemptyArray);
                if(!count($isemptyArray)){
                    $this->ret['other']['msg'] = '代金券不可用,请重新选择';
                }else{
                     //获取适合条件的所有order中价格最高的order,价格一样，取orderid最小的
                    foreach($resOrder as $key2 => $value2){
                        $r[$key2] = $value2['price'];
                        $n[$key2] = $value2['orderid'];
                    }              
                    array_multisort($r,SORT_DESC,$n,SORT_ASC,$resOrder);
                    $chooseOrder = $resOrder[0];
                    //print_r($chooseOrder);exit;
                    //通过订单号找到项目name
                    /*
                    $voucherItemName = M('order_item')->where(array('ordersn' => $chooseOrder['orderSn']))->getField('itemname');
                     */
                    $chooseOrderInfo = D('OrderItem')->getOrderItemByOrderId($chooseOrder['orderid']);
                    $voucherItemName = $chooseOrderInfo['itemname'];

                    //代金券可以使用,获取抵用金额
                    $useMoneyByVoucher = $voucherObj->getUseMoneyByVoucherId($chooseOrder['orderSn'],$vId);  
                    //获取代金券活动名称
                    /*
                    $vcTitle = M('voucher')->where(array('vId' => $vId))->getField('vcTitle');
                     */
                    $voucher = $voucherObj->getVoucherById($vId);
                    $vcTitle = $voucher['vcTitle'];
                    $this->ret['other']['useMoneyByVoucher'] = $useMoneyByVoucher;
                    $this->ret['other']['vId'] = $vId;
                    $this->ret['other']['vcTitle'] = $vcTitle;
                    $this->ret['other']['totalMoney'] = intval($totalPriceDis - $useMoneyByVoucher);
                    $this->ret['other']['voucherItemName'] = $voucherItemName;
                    $this->ret['other']['vOrderSn'] = $chooseOrder['orderSn'];
                    $this->ret['other']['isenough'] = (($userInfo['money']+$useMoneyByVoucher-$totalPriceDis)>=0) ? 1 : 0;
                    $this->ret['other']['paymoney'] = (($userInfo['money']+$useMoneyByVoucher-$totalPriceDis)>=0) ? 0 : intval($totalPriceDis-$userInfo['money']-$useMoneyByVoucher);
                    //判断此订单是否有绑定过代金券
                    /*
                    $res1 =  M('voucher')->where(array('vOrderSn' => $chooseOrder['orderSn']))->count();
                    if($res1){
                        //如果有绑定过，将其解绑,把订单号更新为空
                        M('voucher')->where(array('vOrderSn' => $chooseOrder['orderSn'] ,'vId' => array('neq',$vId)))->save(array('vOrderSn' => ''));
                    }
                     */
                    $orderVoucher = $voucherObj->getVocherByOrdersn($chooseOrder['orderSn']);
                    if(!empty($orderVoucher))
                        $voucherObj->unbindOrder($orderVoucher['vId']);
                }

            }else{
                if(!empty($allUniqueVoucherIds[0])){

                    $vId = $allUniqueVoucherIds[0];
                    //金额最高的代金券，选出最适合的项目orderSn
                    //根据购物车号获取订单号
                    /*
                    $allOrderSn = M('order')->field('ordersn,orderid')->where(array('shopcartsn' => $shopcartSn))->order('orderid')->select();
                     */
                    //print_r($allOrderSn);
                    $shopcartOrders = D('Order')->getOrderByShopcartSn($shopcartSn);
                    $allOrderSn = array();
                    foreach($shopcartOrders as $shopcartOrder)
                        $allOrderSn[] = array(
                            'ordersn' => $shopcartOrder['ordersn'],
                            'orderid' => $shopcartOrder['orderId'],
                            'priceall' => $shopcartOrder['priceall'],
                        );
                    $i = 0;
                    foreach ($allOrderSn as $key => $value) {
                        //判断每一个订单，查询哪些订单可以使用此券
                        $vidCanBeUsedRes = $voucherObj->isVidCanBeUsed($vId,$userId,$value['ordersn']);                        
                        //如果可以使用，查询订单金额
                        if($vidCanBeUsedRes){
                            /*
                            $resOrder[$i]['price'] = M('order')->where(array('ordersn' => $value['ordersn']))->getField('priceall');
                             */
                            $resOrder[$i]['price'] = $value['priceall'];
                            $resOrder[$i]['orderSn'] = $value['ordersn'];
                            $resOrder[$i]['orderid'] = $value['orderid'];
                            $i++;
                        }
                        //print_r($resOrder);exit;
                    }
                    //为空则说明此券不可用  (此处不需要判断)
                    /*
                    foreach ($resOrder as $key3 => $value3) {
                        $isemptyArray[] = $value3['orderSn'];
                    }
                    if(!count($isemptyArray)){
                        $this->error(7005);
                    }
                    */
                    //获取适合条件的所有order中价格最高的order,价格一样，取orderid最小的

                    foreach($resOrder as $key2 => $value2){
                        $r[$key2] = $value2['price'];
                        $n[$key2] = $value2['orderid'];
                    }              
                    array_multisort($r,SORT_DESC,$n,SORT_ASC,$resOrder);
                    //print_r($resOrder);exit;
                    $chooseOrder = $resOrder[0];
                    //通过订单号找到项目name
                    /*
                    $voucherItemName = M('order_item')->where(array('ordersn' => $chooseOrder['orderSn']))->getField('itemname');
                     */
                    $chooseOrderInfo = D('OrderItem')->getOrderItemByOrderId($chooseOrder['orderid']);
                    $voucherItemName = $chooseOrderInfo['itemname'];

                    //代金券可以使用,获取抵用金额
                    $useMoneyByVoucher = $voucherObj->getUseMoneyByVoucherId($chooseOrder['orderSn'],$vId);
                    //echo $useMoneyByVoucher;exit;
                    //获取代金券活动名称
                    /*
                    $vcTitle = M('voucher')->where(array('vId' => $vId))->getField('vcTitle');
                     */
                    $voucher = $voucherObj->getVoucherById($vId);
                    $vcTitle = $voucher['vcTitle'];
                    $this->ret['other']['useMoneyByVoucher'] = $useMoneyByVoucher;
                    $this->ret['other']['vId'] = $vId;
                    $this->ret['other']['vcTitle'] = $vcTitle;
                    $this->ret['other']['totalMoney'] = intval($totalPriceDis - $useMoneyByVoucher);
                    $this->ret['other']['voucherItemName'] = $voucherItemName;
                    $this->ret['other']['vOrderSn'] = $chooseOrder['orderSn'];
                    $this->ret['other']['isenough'] = (($userInfo['money']+$useMoneyByVoucher-$totalPriceDis)>=0) ? 1 : 0;
                    $this->ret['other']['paymoney'] = (($userInfo['money']+$useMoneyByVoucher-$totalPriceDis)>=0) ? 0 : intval($totalPriceDis-$userInfo['money']-$useMoneyByVoucher);
                    //判断此订单是否有绑定过代金券
                    /*
                    $res2 =  M('voucher')->where(array('vOrderSn' => $chooseOrder['orderSn']))->count();
                    if($res2){
                        //如果有绑定过，将其解绑,把订单号更新为空
                        M('voucher')->where(array('vOrderSn' => $chooseOrder['orderSn'] ,'vId' => array('neq',$vId)))->save(array('vOrderSn' => ''));
                    }
                     */
                    $orderVoucher = $voucherObj->getVocherByOrdersn($chooseOrder['orderSn']);
                    if(!empty($orderVoucher))
                        $voucherObj->unbindOrder($orderVoucher['vId']);
                }          
            }   
        }else{
            //判断购物车中每一个项目是否有绑定过代金券的,有就将其解绑
            /*
            $allOrderSn = M('order')->field('ordersn')->where(array('shopcartsn' => $shopcartSn))->select();
            foreach ($allOrderSn as $key => $value) {
                //判断此订单是否有绑定过代金券
                $res =  M('voucher')->where(array('vOrderSn' => $value['ordersn']))->count();
                if($res){
                    //如果有绑定过，将其解绑,把订单号更新为空
                    M('voucher')->where(array('vOrderSn' => $value['ordersn']))->save(array('vOrderSn' => ''));
                    //获取订单的价格
                    $priceall = M('order')->where(array('ordersn' => $value['ordersn']))->getField('priceall');
                    //修改订单的实付金额
                    M('order')->where(array('ordersn' => $value['ordersn']))->save(array('actuallyPay' => $priceall));
                }
            }
             */
            $shopcartOrders = D('Order')->getOrderByShopcartSn($shopcartSn);
            foreach($shopcartOrders as $shopcartOrder)
            {
                $orderVoucher = $voucherObj->getVocherByOrdersn($shopcartOrder['ordersn']);
                if(!empty($orderVoucher))
                {
                    $voucherObj->unbindOrder($orderVoucher['vId']);
                    D('Order')->updateOrderActuallyPay($shopcartOrder['ordersn'], $shopcartOrder['priceall']);
                }
            }
        }           
        //print_r($ret);
        $this->success();
    }


    /**
     * 余额支付 + 代金券
     */
    public function moneyPay(){
        $shopcartsn = $this->param['shopcartsn'];
        $userDeviceInfo = $this->from;
        if(!$shopcartsn){
            $this->error(1);
        }
        //传入代金券vId
        $useMoneyByVoucher = 0;
        $bindRes = '';
        if($this->param['vId']){
            $vId = intval($this->param['vId']);
            //查找代金券是否绑定过
            $voucherObj = D('Voucher');
            $bindRes = $voucherObj->isVidBind($vId);
            if(!$bindRes){
                $this->error(7007);
            }
            //判断返回的订单是否属于此购物车
            /*
            $res = M('order')->where(array('ordersn' => $bindRes, 'shopcartsn' => $shopcartsn))->find();
            if(!$res){
                $this->error(7001);
            }
             */
            $order = D('Order')->getOrderbySn($bindRes);
            if($order['shopcartsn'] != $shopcartsn)
                $this->error(7001);
             //代金券可以使用,获取抵用金额
            $useMoneyByVoucher = $voucherObj->getUseMoneyByVoucherId($bindRes,$vId);
        }
        
        
        //通过购物车号来查询购物车中每一个项目的数量，对数量判断库存
        /*
        $shopcartList = M('shopcart')->where("shopcartsn = '%s'",$shopcartsn)->select();
         */
        $shopcartList = D('ShopCart')->getAllShopCartBySn($shopcartsn);
        foreach ($shopcartList as $shopcartV){
            $nums = $shopcartV['nums'];
            //判断当前项目数量是否大于库存
            /*
            $itemInfo = M('salon_item')->field('total_rep,sold')->where('status = 1 and itemid = '.$shopcartV['itemId'])->find(); //项目
             */
            $itemInfo = D('SalonItem')->getItemInfo($shopcartV['itemId']);
            if(1==$itemInfo['status']){
                $itemInfo['total_rep'] = $itemInfo['totalRep'];
                $itemInfo['sold'] = $itemInfo['sold'];
            }
            //echo M('salon_item')->getLastSql();exit;
            if(!$itemInfo){
                $this->error(2003,': '.$shopcartV['salonName'].'-'.$shopcartV['itemName']);               
            }
            //检测库存
            if($itemInfo['total_rep']){
                $aboutSellNum=$itemInfo['sold']+$nums;
                if($aboutSellNum>$itemInfo['total_rep']){
                    $this->error(2902,': '.$shopcartV['salonName'].'-'.$shopcartV['itemName']);  //项目已经售罄
                }
            }            
        }
        
        
        /*
        $UserObj = M('user');
        $user = $UserObj->field('costpwd,money')->find($this->userId);
         */
        $UserObj = D('User');
        $user=$UserObj->getUserById($this->userId);

        $OrderObj=D('Order');
        $where['shopcartsn']=$shopcartsn;
        $where['user_id']=$this->userId;
        $where['ispay']=1;
        /*
        $orderList=$OrderObj->where($where)->select();
         */
        $orderList = $OrderObj->getOrderByShopCartSn($shopcartsn,$this->userId,$ispay);
        //print_r($orderList);exit;
        if(!$orderList){
            //echo $OrderObj->_sql();
            $this->error(2921);
        }
        //print_r($orderList);die();
        $totalMoney=0;
        foreach($orderList as $tlistV){
            $totalMoney+=$tlistV['priceall'];
        }
  
        $needPayPrice = $totalMoney - $useMoneyByVoucher ;
        if($user['money']<$needPayPrice){
            $this->error(1001);
        }
        /*
        if($user['money']<$totalMoney){
            $this->error(1001);
        }
         */
        $UserObj->startTrans();
        /*
        $OrderItemObj=M('order_item');
        $SalonObj=M('salon');
         */
        $OrderItemObj = D('OrderItem');
        $SalonObj=D('salon');
        $OrderObj=D('Order');

        foreach($orderList as $listV){
            //订单已支付，不需重复处理
            if($listV['ispay'] == 2)
                continue;
            /*
            $itemInfo=$OrderItemObj->where("ordersn='".$listV['ordersn']."'")->find();
             */
            $orderInfo = $OrderObj->getOrderbySn($listV['ordersn']);
            $orderItemInfo = D('OrderItem')->getOrderItemByOrderId($orderInfo['orderId']);
            $itemInfo = array(
                'order_item_id' => $orderItemInfo['orderItemId'],
                'ordersn' => $orderItemInfo['ordersn'],
                'salonid' => $orderItemInfo['salonId'],
                'itemid' => $orderItemInfo['itemId'],
                'num' => $orderItemInfo['num'],
                'price_dis' => $orderItemInfo['priceDis'],
                'end_time' => $orderItemInfo['endTime'],
                'salonNormsId' => $orderItemInfo['salonNormsId'],
                'user_id' => $orderItemInfo['userId'],
                );
            /*
            $salonInfo=$SalonObj->where('salonname')->find($listV['salonid']);
             */
            $salonInfo=$SalonObj->getSalonById($listV['salonid']);
            //print_r($itemInfo);
            //print_r($listV);
            //die();
            //此处只有一个代金券选中订单的订单调用代金券余额支付
            if($bindRes == $listV['ordersn']){
                $adRs=$OrderObj->todoMoneyPay($userDeviceInfo,$this->userId,$listV,$itemInfo,$useMoneyByVoucher);
                if(!$adRs){
                    $UserObj->rollback();

                    $this->error($OrderObj->getError(),': '.$salonInfo['salonname'].'-'.$itemInfo['itemname']);
                }
                continue;
            }
            $adRs=$OrderObj->todoMoneyPay($userDeviceInfo,$this->userId,$listV,$itemInfo);
            if(!$adRs){
                $UserObj->rollback();

                $this->error($OrderObj->getError(),': '.$salonInfo['salonname'].'-'.$itemInfo['itemname']);
            }
        }
        /*
        $spWhere['userId']=$this->userId;
        $spWhere['shopcartsn']=$shopcartsn;
        $spRs=M('shopcart')->where($spWhere)->delete();
         */
        $spRs=D('ShopCart')->deleteShopCartBySn($shopcartsn,$this->userId);
        if(!$spRs){
            $UserObj->rollback();

            $this->error(2923);
        }

        $UserObj->commit();
        $this->success();
    }


    /**
     * 获取臭美券
     */
    public function requestTicket(){
        $shopcartSn=$this->param['shopcartsn'];

        if(!$shopcartSn){
            $this->error(1);
        }

        /*
        $where['myorder.user_id']=$this->userId;
        $where['shopcartsn']=$shopcartSn;
        $list=D('OrderAndItemView')->field('salonId,ordersn,priceall,order_item_id,itemName,useLimit,extra,itemId')->where($where)->select();
        //echo D('OrderAndItemView')->_sql();
         */
        $orders = D('Order')->getOrderByShopcartSn($shopcartSn);
        if(empty($orders))
            $this->error(1);
        $list = array();
        foreach($orders as $order)
        {
            $orderItem = D('OrderItem')->getOrderItemByOrderId($order['orderId']);
            $list[] = array(
                'itemId' => $orderItem['itemId'],
                'salonId' => $orderItem['salonId'],
                'ordersn' => $order['ordersn'],
                'priceall' => $orderItem['priceall'],
                'order_item_id' => $orderItem['orderItemId'], 
                'itemName' => $orderItem['itemname'],
                'useLimit' => $orderItem['useLimit'],
                'extra' => $orderItem['extra'],
            );
        }
        
        
        if(!$list){
            $this->error(1);
        }
        //print_r($list);
        /*
        $TicketObj=M('order_ticket');
         */
        $SalonItemFormatsViewObj=D('SalonItemFormatsView');

        $haveTicket=0;
        $ticketNum=0;
        $totalPrice=0;
        foreach($list as &$listV){
            $totalPrice+=$listV['priceall'];
            
            /*
            $salonInfo=M('salon')->field('salonname')->find($listV['salonId']);
             */
            $salonInfo = D('Salon')->getSalonById($listV['salonId']);
            if(!$salonInfo){
                $this->error(2002);
            }
            $listV['salonName']=$salonInfo['salonname'];
            /*
            $ticketInfo=$TicketObj->where('order_item_id= %d',$listV['order_item_id'])->find();
             */
            $ticketInfo = D('OrderTicket')->getTicketByOrderItemId($listV['order_item_id']);
            if($ticketInfo){
                $listV['ticketNo']=$ticketInfo['ticketno'];
                $ticketNum++;
            }
            $listV['normsStr']=$SalonItemFormatsViewObj->getFormatStr($listV['extra']);
            //获取增值服务
            $item=D("SalonItem")->getItemInfo($listV['itemId']);
            $listV['useLimit']=$listV['useLimit'];
            $listV['addedService'] = array();
            if (!empty($item['addserviceStr'])) 
            {
                //先判断当前的增值服务和分类的增值服务是否符合
                $addserviceStr = rtrim(trim($item['addserviceStr'], ","), ',');
                $addserviceArr = explode(",", $addserviceStr);
                $addedServiceItemType = D('AddServiceItemType')->getItemTypeAddService($item['typeid']);
                if (!empty($addedServiceItemType) && !empty($addedServiceItemType['serviceDetail'])) 
                {
                    $serviceDetailStr = trim($addedServiceItemType['serviceDetail'], ",");
                    $serviceDetailArr = explode(",", $serviceDetailStr);
                    $diff = array_diff($addserviceArr, $serviceDetailArr);
                    if(count($diff)>0)
                        $addService = D('AddService')->getAddServicesById($serviceDetailArr);
                    else 
                        $addService = D('AddService')->getAddServicesById($addserviceArr);
                }
                $listV['addedService'] = $addService;
            }
            unset($listV['salonId']);
            unset($listV['order_item_id']);
            unset($listV['priceall']);
            unset($listV['extra']);
        }
        //var_dump($ticketNum);
        if($ticketNum==count($list)){
            $haveTicket=1;
        }

        $this->ret['main']=$list;
        $this->ret['other']=array('haveTicket'=>$haveTicket,'growth'=>$totalPrice);

        $this->success();
    }
    
}