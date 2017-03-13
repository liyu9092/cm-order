<?php
namespace Home\Model;

class MovieticketModel extends BaseModel{

    /**
     * 通过短信发送券信息
     * @param $smsStr
     * @param $ticketInfo
     * @return bool
     */
    public function sendTicketMsgToMobile($mobilephone,$smsStr,$ticketInfo){
        //$smsStr=$confInfo['sms_tmpl_ticket'];
        $keyArr=['GIFT_TICKET_NUM','GIFT_TICKET_PWD'];
        $valArr=[$ticketInfo['ticketNum'],$ticketInfo['ticketPwd']];
        $smsStr=str_replace($keyArr,$valArr,$smsStr);

        //echo $smsStr;
        //die();
        try{
            sendphonemsg($mobilephone,$smsStr);
        }catch (Exception $e){
            $this->error=$e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 当该用户未领取过券时，占用一个可用券
     * @param $userId           用户id
     * @param $mobilephone      用户手机号
     * @param $giftCouponId     资源库id
     * @param $eventConfId      活动id
     * @return bool|mixed
     */
    public function useATicket($userId,$mobilephone,$giftCouponId,$eventConfId){
        $chWhere['userId']=$userId;
        $chWhere['giftCouponId']=$giftCouponId;
        $chWhere['eventConfId']=$eventConfId;
        $isSend=$this->field('userId')->where($chWhere)->find();
        //检测资源是否已经发放过
        if($isSend){
            $this->error='movieticket is send';
            return false;
        }

        //获取一个未使用的券
        $findWhere['giftCouponId']=$giftCouponId;
        $findWhere['sendType']=0;
        $movieTicketInfo=$this->where($findWhere)->find();
        if(!$movieTicketInfo){
            $this->error='movieticket is over';
            return false;
        }

        $useRs=$this->toUseATicket($userId,$mobilephone,$movieTicketInfo['id'],$giftCouponId,$eventConfId);
        if(!$useRs){
            $this->error='movieticket use failed';
            return false;
        }
        return $movieTicketInfo;
    }


    /**
     * 占用一个券
     * @param $userId           用户id
     * @param $mobilephone      用户手机号
     * @param $ticketId         资源id (券id)
     * @param $giftCouponId     资源库id
     * @param $eventConfId      活动id
     * @return bool
     */
    public function toUseATicket($userId,$mobilephone,$ticketId,$giftCouponId,$eventConfId){
        //占位礼品券
        $upData['userId']=$userId;
        $upData['mobilephone']=$mobilephone;
        $upData['eventConfId']=$eventConfId;
        $upData['sendType']=1;
        $upData['useTime']=time();

        $upWhere['id']=$ticketId;
        $upWhere['sendType']=0;
        $upWhere['giftCouponId']=$giftCouponId;
        $setRs=$this->where($upWhere)->save($upData);

        return $setRs;
    }
}