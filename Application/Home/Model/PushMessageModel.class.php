<?php
/****
 * 统一消息推送
 * by 胡亮
 ****/

namespace Home\Model;
use Think\Model;
use Think\Log;

class PushMessageModel extends BaseModel {
    /***
     * 先写入push表，然后推送消息
     * $msgType 的值目前针对用户端： 1 赏金任务服务完成，跳转未打赏记录（任务详情）
     *                               2 项目过期提醒，跳转臭美券详情
     *                               3 代金券即将过期，跳转到我的代金券
     *                               4 取消赏金任务,跳转取消的任务-任务详情
     *                               5 项目消费成功,跳转我的臭美-服务评价
     *                               6 用户获取到代金券,跳转我的代金券
     *
     */
    public function addAndPushMessage($userId,$title,$desc,$payloadData,$msgType,$logMessage){
        if(!$userId || !$title || !$desc || !$payloadData || !$msgType ){
            return false;
        }
        $user = D('User')->getUserById($userId);
        $user['os_type'] = $user['osType'];
        $appType  = '';
        if($user['os_type'] == 1)//Android
            $appType="android";
        elseif($user['os_type'] == 2) //Ios
            $appType="ios";
        $payload = array(
            'msgType' => $msgType,
            'title' => $title,
            'desc' => $desc,
            'data' => $payloadData,
        );
        $data = array(
            'title' => $title,
            'desc' => $desc,
            'payload' => json_encode($payload),
            'notifyForeground' => 1,
            'passThrough' => 1,
            'appType'=>$appType,
            'badge' => 1,
            'app' => 'v1',
        );
        $targetList = array(D('Des')->encrypt($userId));
        //将推送的数据存入数据库
        $pushData = array(
            array(
                'receiverUserId' => $userId,
                'type' => 'USR',
                'osType' => strtoupper($appType),
                'title' => $title,
                'message' => $desc,
                'priority' => 1,
                'event' => json_encode($payloadData),
                'status' => 'SNT',
                'createTime' => time(),
                'updateTime' => time(),
            )
        );
        $res = D('Comment')->addPushs($pushData);
        if($res){
            if(!empty($appType)){
                D("Push")->sendToAliases($data,$targetList);
            }else{
                Log::write("用户手机设备未能识别，无法推送,用户id:".$userId);
            }          
        }else{
            if($logMessage){
                Log::write($logMessage);
            }else{
                Log::write("推送消息写入失败");
            }

        }
    }

}