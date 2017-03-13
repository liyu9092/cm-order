<?php
namespace Home\Model;
use Think\Model;

/**
 * 臭美app发型 用户上传 展示列表所用
 * @deprecated since version thrift_150709 红包功能，已废弃很久
 * @author andyyang
 * more: http://www.webyang.net
 */
class PacketModel extends BaseModel {


    /**
     * 是否可以发红包 已经发红包连接
     * @param $data
     * @param $packeturl
     * @return mixed
     */
    public function judge($data,$packeturl) {

        if(!$data) return;

        $isopen = M('packet_set')->getField('isopen'); //查看红包开启状态

        foreach($data as $key => $val) {
            $flag = 0; //0:不可以,1:可以
            //屏蔽红包
            if(!$isopen) { //开启红包
                $where['ticketno'] = $val['order_ticket_id'];
                $packet = M('packet')->field('allow_num,used_num,end_time')->where($where)->find();

                //没发红包 或者 使用数量小于总数量 并且 没结束
                if(!$packet){
                    $flag = 1;
                }else{
                    if($packet['allow_num']>$packet['used_num'] && $packet['end_time']>time()) {
                        $flag = 1;
                    }
                }
            }
            if($flag){
                $data[$key]['packeturl'] = $packeturl.'/redPacket?ticketno='.$val['order_ticket_id'];
            }
            $data[$key]['ispacket'] = $flag;
        }

        return $data;
    
    }


    /**
     * 新注册的手机号是否抢过红包、有则把钱给他
     * @param $mobilephone
     */
    public function getPacketMoneyByPhone($mobilephone,$userid){
        $where['user_id']=0;
        $where['mobile']=$mobilephone;
        $where['iscount']=1;

        $PacketInfoObj=M('packet_info');
        $packetInfo=$PacketInfoObj->field('packet_info_id,money')->where($where)->find();
        if(!$packetInfo){
            return false;
        }
        if($packetInfo['money']>1000){
            return false;
        }

        $PacketInfoObj->startTrans();

        //用户余额增加、统计用户金额
        $pRs=D('Cmpacketstatic')->addMoneyAndPacketCount($userid,$packetInfo['money']);
        if(!$pRs){
            $PacketInfoObj->rollback();
            return false;
        }
        //修改红包的统计状态
        $piRs=$PacketInfoObj->where('packet_info_id='.$packetInfo['packet_info_id'])->setField('iscount',2);
        if(!$piRs){
            $PacketInfoObj->rollback();
            return false;
        }

        $PacketInfoObj->commit();
    }
}
