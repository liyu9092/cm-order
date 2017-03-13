<?php
/****
 * @author zhenhua.zhang
 * @desc 	push
 * @time 2015-08-04
 ****/
namespace Home\Model;
use		Think\Model;
/**
 * @deprecated since version thrift_150709
 */
class PushModel extends BaseModel {
    
    /**
     * 推送到别名
     */
    public function sendToAliases($data, $targetList){
        
        $thrift = D('ThriftHelper');
        $push= new \cn\choumei\thriftserver\service\stub\gen\Push();
        
        $push->title=$data['title'];
        $push->desc=$data['desc'];
        $push->payload=$data['payload'];
        $push->passThrough=$data['passThrough'];
        $push->notifyForeground=$data['notifyForeground'];
        $push->notifyId=$data['notifyId'];        
        $push->targetType=$data['targetType'];
        
        $push->app=$data['app'];
        $push->appType=$data['appType'];
        
        $push->soundUrl=$data['soundUrl']; 
        $push->badge=$data['badge'];       
        
        $result = $thrift->request('push-center', 'sendToAliases', array($push, $targetList));
        return $result;
    }
    
    /**
     * 推送到reg id
     */
    public function sendToIds($data, $targetList){
        
        $thrift = D('ThriftHelper');
        $push= new \cn\choumei\thriftserver\service\stub\gen\Push();
        
        $push->title=$data['title'];
        $push->desc=$data['desc'];
        $push->payload=$data['payload'];
        $push->passThrough=$data['passThrough'];
        $push->notifyForeground=$data['notifyForeground'];
        $push->notifyId=$data['notifyId'];        
        $push->targetType=$data['targetType'];
        
        $push->app=$data['app'];
        $push->appType=$data['appType'];
        
        $push->soundUrl=$data['soundUrl']; 
        $push->badge=$data['badge'];       
        
        $result = $thrift->request('push-center', 'sendToIds', array($push, $targetList));
        return $result;
    }
    
    /**
     * 推送到标签
     */
    public function multiTopicBroadcast($data, $targetList){
        
        $thrift = D('ThriftHelper');
        $push= new \cn\choumei\thriftserver\service\stub\gen\Push();
        
        $push->title=$data['title'];
        $push->desc=$data['desc'];
        $push->payload=$data['payload'];
        $push->passThrough=$data['passThrough'];
        $push->notifyForeground=$data['notifyForeground'];
        $push->notifyId=$data['notifyId'];        
        $push->targetType=$data['targetType'];
        
        $push->app=$data['app'];
        $push->appType=$data['appType'];
        
        $push->soundUrl=$data['soundUrl']; 
        $push->badge=$data['badge'];       
        
        $result = $thrift->request('push-center', 'multiTopicBroadcast', array($push, $targetList));
        return $result;
    }
    
    /**
     * 推送到全部用户
     */
    public function broadcastAll($data){
        
        $thrift = D('ThriftHelper');
        $push= new \cn\choumei\thriftserver\service\stub\gen\Push();
        
        $push->title=$data['title'];
        $push->desc=$data['desc'];
        $push->payload=$data['payload'];
        $push->passThrough=$data['passThrough'];
        $push->notifyForeground=$data['notifyForeground'];
        $push->notifyId=$data['notifyId'];        
        $push->targetType=$data['targetType'];
        
        $push->app=$data['app'];
        $push->appType=$data['appType'];
        
        $push->soundUrl=$data['soundUrl']; 
        $push->badge=$data['badge'];       
        
        $result = $thrift->request('push-center', 'broadcastAll', array($push));
        return $result;
    }
}