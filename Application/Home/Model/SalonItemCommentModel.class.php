<?php
namespace Home\Model;
use Think\Model;

/**
 * 臭美app发型 分组 展示列表所用
 * @author andyyang
 * more: http://www.webyang.net
 */
class SalonItemCommentModel extends BaseModel {
	
    protected $tableName   = 'salon_itemcomment';
    
    const PLEASANT    = 1;
    const SATISFY     = 2;
    const UNSATISFY   = 3;
    
    public function getRemark($remark = ''){
        $array = array(
            0=>'',
            1=>'服务不满意',
            2=>'发型不满意',
            3=>'乱收费',
        );
        if(isset($remark)&&is_numeric($remark))
        {
            return $array[$remark];
        }
        return $array;
    }


    /**
     * 评论操作
     * @param $data
     * @param $satisfy
     * @return bool|mixed
     */
    public function doComment($data,$satisfy){
        /*
        $model = M('salon_itemcomment');
        $model->startTrans();

        $result1 = $model->add($data);

        //修改相应店铺的评论数量
        $result2 = M('salon')
            ->where('salonid= %d',$data['salonid'])
            ->setField(
            array(
                $satisfy=>array('exp',$satisfy.'+1'),
                'commentnum'=>array('exp','commentnum+1'),
            ));
        //修改项目的评论数量
        $result3 = M('salon_item')
            ->where('itemid= %d',$data['itemid'])
            ->setField(
            array(
                $satisfy=>array('exp',$satisfy.'+1'),
                'commentNum'=>array('exp','commentNum+1'),
            ));
        //修改评论的状态
        $result4 = M('order_ticket')
            ->where('order_ticket_id= %d',$data['order_ticket_id'])
            ->setField('iscomment',2);
        //获取评论的信息
        if($result1&&$result2&&$result3){
            $model->commit();
            $return = M('salon_item')
                ->field(array('itemid as itemId','salonid as salonId','satisfyOne','satisfyTwo','satisfyThree'))
                ->where('itemid= %d',$data['itemid'])
                ->find();
            $return['itemcommentid'] = $result1;
            return $return;
        }
        else{
            $model->rollback();
            return false;
        }
         */
        
        $param = new \cn\choumei\thriftserver\service\stub\gen\PostItemCommentParam();
        $param->content = $data['content'];
        $param->imgsrc = $data['imgsrc'];
        $param->itemId = $data['itemid'];
        $param->orderTicketId = $data['order_ticket_id'];
        $param->remark = $data['satisfyRemark'];
        $param->salonId = $data['salonid'];
        $param->satisfyType = $data['satisfyType'];
        $param->stylistId = $data['hairstylistid'];
        $param->userId = $data['user_id'];
        $thrift = D('ThriftHelper');
        $postRet = $thrift->request('comment-center', 'postItemComment', array($param));
        /*
        $postRet = $thrift->request('comment-center', 'postItemComment', 
            array($data['order_ticket_id'], $data['satisfyType'], $data['satisfyRemark'], $data['content'], $data['imgsrc'], $data['user_id'], $data['salonid'], $data['itemid'], $data['hairstylistid']));
         */
        if(intval($postRet) <= 0)
            return false;
        $commentNumStep = 1;
        $satisfyOneStep = 0;
        $satisfyTwoStep = 0;
        $satisfyThreeStep = 0;
        if($data['satisfyType'] == self::PLEASANT)
            $satisfyOneStep = 1;
        else if($data['satisfyType'] == self::SATISFY)
            $satisfyTwoStep = 1;
        else if($data['satisfyType'] == self::UNSATISFY)
            $satisfyThreeStep = 1;
        else 
            return false;
        
        #2 修改项目评论状态
        D('OrderTicket')->updateTicketIsComment($data['order_ticket_id']);
        
        #2 修改项目评论数据
        D('SalonItem')->updateComment($data['itemid'], $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep);
        
        #3 修改店铺评论数据
        D('Salon')->updateComment($data['salonid'], $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep);
        
        $item = D('SalonItem')->getItemById($data['itemid']);
        if(empty($item))
            return null;
        $ret = array(
            'itemId' => $item['itemid'],
            'salonId' => $item['salonid'], 
            'satisfyOne' => $item['satisfyOne'],
            'satisfyTwo' => $item['satisfyTwo'], 
            'satisfyThree' => $item['satisfyThree'],
            );
        return $ret;

    }

    /**
     * 修改评论操作
     * By zhigui.zhang@choumei.cn
     * Date 2015-04-23
     */
    public function changeComment($data,$satisfy){
        /*
        //修改评论时不去修改时间
        unset($data['add_time']);
        $model = M('salon_itemcomment');
        $model->startTrans();
        //$result1 = $model->where('order_ticket_id='.$data['order_ticket_id'].' and salonid='.$data['salonid'].' and itemid='.$data['itemid']. ' and user_id='.$data['user_id'])->save($data);
        $where = array(
            'order_ticket_id' => $data['order_ticket_id'],
            'salonid' => $data['salonid'],
            'itemid' => $data['itemid'],
            'user_id' => $data['user_id'],
            'satisfyType' => 3   //必须是不满意
        );
        $result1 = $model->where($where)->save($data);
        $result2 = M('salon')
            ->where('salonid='.$data['salonid'])
            ->setField(
                array(
                    $satisfy=>array('exp',$satisfy.'+1'),
                    'satisfyThree'=>array('exp', 'satisfyThree-1'),
                ));
        //修改项目的评论数量
        $result3 = M('salon_item')
            ->where('itemid='.$data['itemid'])
            ->setField(
                array(
                    $satisfy=>array('exp',$satisfy.'+1'),
                    'satisfyThree'=>array('exp','satisfyThree-1'),
                ));

        //获取评论的信息
        if($result1&&$result2&&$result3){
            $model->commit();
            $return = M('salon_item')
                ->field(array('itemid as itemId','salonid as salonId','satisfyOne','satisfyTwo','satisfyThree'))
                ->where('itemid='.$data['itemid'])
                ->find();
            return $return;
        }
        else{
            $model->rollback();
            return false;
        }
         */
        
        $comment = $this->getCommentByTicketId($data['order_ticket_id']);
        //评论不存在或越权修改其他人的评论
        if(empty($comment) || $comment['userId'] != $data['user_id'])
            return false;
        //只有原评论为不满意才能修改
        if($comment['satisfyType'] != self::UNSATISFY || $data['satisfyType'] == $comment['satisfyType'])
            return false;
        
        #1 改评论
        $thrift = D('ThriftHelper');
        $changCommentRet = $thrift->request('comment-center', 'changeItemComment', array($data['order_ticket_id'], $data['satisfyType'], $data['satisfyRemark'], $data['content'], $data['imgsrc']));
        
        if($changCommentRet != 1)
            return false;
        $commentNumStep = 0;
        $satisfyOneStep = 0;
        $satisfyTwoStep = 0;
        $satisfyThreeStep = -1;
        if($data['satisfyType'] == self::PLEASANT)
            $satisfyOneStep = 1;
        else if($data['satisfyType'] == self::SATISFY)
            $satisfyTwoStep = 1;
        else 
            return false;
        #2 修改项目评论数据
        D('SalonItem')->updateComment($data['itemid'], $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep);
        
        #3 修改店铺评论数据
        D('Salon')->updateComment($data['salonid'], $commentNumStep, $satisfyOneStep, $satisfyTwoStep, $satisfyThreeStep);
        
        $item = D('SalonItem')->getItemById($data['itemid']);
        if(empty($item))
            return null;
        $ret = array(
            'itemId' => $item['itemid'],
            'salonId' => $item['salonid'], 
            'satisfyOne' => $item['satisfyOne'],
            'satisfyTwo' => $item['satisfyTwo'], 
            'satisfyThree' => $item['satisfyThree'],
            );
        return $ret;
        
    }

    /**
     * 评论详情
     * @param string $where
     * @return mixed
     */
    public function comment($where){
        /*
        $prefix = C('DB_PREFIX');
        $model = M('salon_itemcomment as comment');
        $data = $model
            ->where($where)
            ->field(array(
            'item.itemid as itemId',
            'item.itemname as itemName',
            'salon.salonid as salonId',
            'salon.salonname as salonName',
            'comment.add_time as addTime',
            'comment.satisfyType as satisfyType',
            'comment.satisfyRemark as satisfyRemark',
            'comment.content as content',
            'comment.imgsrc as img',
            'comment.reply as reply',
        ))
            ->join($prefix.'salon_item as item on item.itemid=comment.itemid')
            ->join($prefix.'salon as salon on salon.salonid=comment.salonid')
            ->find();
        return $data;
         */
        
        $comment = $this->getCommentByTicketId($where['comment.order_ticket_id']);
        if(empty($comment))
            return null;
        $item = D('SalonItem')->getItemById($comment['itemId']);
        if(empty($item))
            return null;
        $salon = D('Salon')->getSalonById($item['salonid']);
        if(empty($salon))
            return null;
        $ret = array(
            'itemId' => $item['itemid'],
            'itemName' => $item['itemname'],
            'salonId' => $item['salonid'],
            'salonName' => $salon['salonname'],
            'addTime' => $comment['addTime'],
            'satisfyType' => $comment['satisfyType'],
            'satisfyRemark' => $comment['satisfyRemark'],
            'content' => $comment['content'],
            'img' => $comment['imgsrc'],
            'reply' => $comment['reply'],
        );
        return $ret;
    }

    /**
     * 初始化评价
     *
     */
    public function commentInit($data){
        /*
        $model = M('salon_itemcomment');
        $return = M('salon_item')
            ->field(array('itemid as itemId','salonid as salonId','satisfyOne','satisfyTwo','satisfyThree'))
            ->where('itemid='.$data['itemid'].' and salonid = '.$data['salonid'])
            ->find();
        $remark = array_values(array_filter($this->getRemark()));
        $return['remark'] = $remark;
        return $return;
         */
        $item = D('SalonItem')->getItemById($data['itemid']);
        if(empty($item) || $item['salonid'] != $data['salonid'])
            return null;
        
        $return = array(
            'remark' => array_values(array_filter($this->getRemark())),
            'itemId' => $item['itemid'],
            'salonId' => $item['salonid'],
            'satisfyOne' => $item['satisfyOne'],
            'satisfyTwo' => $item['satisfyTwo'],
            'satisfyThree' => $item['satisfyThree'],
        );
        return $return;
    }
    
    public function getCommentByTicketId($orderTicketId)
    {
        $thrift = D('ThriftHelper');
        return $thrift->request('comment-center', 'getCommentByTicketId', array($orderTicketId));
        /*
        return M('salon_itemcomment')->where(array('order_ticket_id' => $orderTicketId))->find();
         */
    }
}
