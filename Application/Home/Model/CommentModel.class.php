<?php
/****
 * @author zhenhua.zhang
 * @desc 	push
 * @time 2015-08-04
 ****/
namespace Home\Model;
use Think\Model;
/**
 * @deprecated since version thrift_150709
 */
class CommentModel extends BaseModel {
    
    public function addPushs($datas){
        $thrift = D('ThriftHelper');        
        
        $pushs=array();
        foreach ($datas as $key=>$data) {
            $push= new \cn\choumei\thriftserver\service\stub\gen\PushThrift();
            $push->configId=$data["configId"];
            $push->receiverUserId=$data["receiverUserId"];
            $push->type=$data["type"];
            $push->osType=$data["osType"];
            $push->title=$data["title"];
            $push->message=$data["message"];        
            $push->expiry=$data["expiry"];
            $push->timing=$data["timing"];  
            $push->priority=$data["priority"];
            $push->event=$data["event"];   
            $push->status=$data["status"]; 
            $push->createTime=$data["createTime"];   
            $push->updateTime=$data["updateTime"]; 
            $pushs[]=$push;
        }      
        $result = $thrift->request('comment-center', 'addPushs', array($pushs));
        return $result;
    }
}