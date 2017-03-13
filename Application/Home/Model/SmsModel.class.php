<?php

/* * **
 * @author lufangrui
 * @desc 	salonitem表相关操作
 * ** */

namespace Home\Model;

use Think\Model;

class SmsModel extends Model {
    /*
     * @type 1. 登陆验证码；2. 臭美券；3. 代金券 4;赏金单  5;项目过期提醒
     */

    public function sendSmsByType($mobilephone, $content, $type = 2) {
        $ip = get_client_ip(0,TRUE);
        $thrift = D('ThriftHelper');
        \Think\Log::write("sendSmsByType:mobilephone:{$mobilephone}----content:{$content}---type:{$type}----ip:{$ip}");
        $res = $thrift->request('sms-center', 'sendSmsByType', array($mobilephone, $content, $ip, $type));
        \Think\Log::write("sendSmsByType:result:{$res}");
        return $res;
    }

    public function sendSms($mobilephone, $content) {
        $thrift = D('ThriftHelper');
        return $thrift->request('sms-center', 'sendSms', array($mobilephone, $content));
    }

    public function repalceSms($smsText, $repalceArray = array()) {

        $smsText = str_replace('{BOUNTY_PAY}', $repalceArray['bountyPay'], $smsText);
        $smsText = str_replace('{TICKETNO}', $repalceArray['ticketNo'], $smsText);
        $smsText = str_replace('{ITEM_NAME}', $repalceArray['itemName'], $smsText);
        $smsText = str_replace('{SALON_NAME}', $repalceArray['salonName'], $smsText);

        return $smsText;
    }

}
