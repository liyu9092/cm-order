<?php
namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller {
    protected $code;                                //消息体参数
    protected $param;                               //body中的参数
    protected $from = array();                               //from中的参数(转为了数组)
    protected $ret=array('main'=>'','other'=>'');   //返回值

    //可访问链接
    protected $limitOri=array(
        'Index'=>array('index'),
        'Inside'=>array('index'),
    );


    public function _initialize(){
        $this->validationUrl();
    }


    /**
     * 验证请求
     */
    private function validationUrl(){
        $ctlName=CONTROLLER_NAME;
        $actName=ACTION_NAME;
        //echo $ctlName,$actName;
        if(isset($this->limitOri[$ctlName])){
            $funcArr=$this->limitOri[$ctlName];
            if(empty($funcArr)){
                $this->error(7);
            }
            if(!in_array($actName,$funcArr)){
                $this->error(7);
            }
        }else{
            $this->error(7);
        }
    }


    /**
     * 处理参数
     */
    protected function parseParam(){ }


    /**
     * 解析参数
     * @param $code
     * @return bool|mixed
     */
    protected function parseCode($code){
        if(!$code){
            return false;
        }
        $code=json_decode(trim($code),true);
        if(!$code || !is_array($code)){
            return false;
        }
        return $code;
    }


    /**
     * 成功提示
     * @param string $data
     */
    protected function success($data=''){
        $result['result']=1;

        if(!$data){
            $data['main']=$this->ret['main'];
            if($this->ret['other']){
                $data['other']=$this->ret['other'];
            }
        }
        if($this->companyData){
            $data['other']=$data['other']?array_merge($data['other'],$this->companyData):$this->companyData;
        }
        if($data){
            $this->rechangeFieldsName($data);
            $result['data']=$data;
        }
        exit(json_encode($result));
    }


    /**
     * 错误提示
     * @param int $code
     * @param string $msg
     */
    protected function error($code=1000,$msg=''){
        $result = array(
            'result' => 0,
        );
        if($code){
            $result['msg']=$this->errormsg($code);
            $result['msg'].=$msg;
            $result['code']=$code;
        }
        exit(json_encode($result));
    }


    /**
     * 数组key值替换成驼峰形式。 可替换N维
     * @param $fields
     * @return bool
     */
    protected function rechangeFieldsName(&$fields){
        if(empty($fields)){
            return false;
        }
        foreach($fields as $fieldsK=>&$fieldsV){
            $newKey=$fieldsK;
            if(is_string($newKey)){
                $tempFieldsV=$fieldsV;
                //把key的首字母全搞成小写
                $newKey[0]=strtolower($newKey[0]);
                //echo $newKey.'<br>';
                if(strstr($newKey,'_')){
                    $temp=explode('_',$newKey);//下划线切割
                    $secKey='';
                    //切割后的元素循环
                    foreach($temp as $tempK=>$tempV){
                        if($tempK>0){
                            //把首字母转成大写
                            $tempV[0]=strtoupper($tempV[0]);
                        }
                        $secKey.=$tempV;
                    }
                    if($secKey){
                        $newKey=$secKey;
                    }
                }
            }

            //递归多纬数组
            if(is_array($fieldsV)){
                $this->rechangeFieldsName($fieldsV);
            }
            //分别存储新的key、value值
            $dataKeys[]=$newKey;
            $dataValues[]=$fieldsV;
        }
        //组合新的数组
        $fields=array_combine($dataKeys,$dataValues);
    }


    /**
     * 调试日志
     * @param $info
     */
    protected function showLog($info) {
        $info = '[' . date('Y-m-d H:i:s') . ']  ' . $info;
        $pre = date('Y-m-d');
        $logName = './logs/' . $pre . '.log';
        $james = fopen($logName, "a+");
        fwrite($james, $info . "\r\n");
        fclose($james);
    }


    /**
     * 获取分页limit参数
     * @param $totalNum         新的总条数
     * @param int $pageSize
     * @return array
     */
    protected function getLimitParam($totalNum,$pageSize=10){
        $page=intval($this->param["page"]); //第一页时 这里为0
        $myPageSize=intval($this->param["pageSize"]);
        if($myPageSize){
            $pageSize=$myPageSize;
        }
        $page=$page>1?$page-1:0;

        $start=$page*$pageSize;  //第一页时 这里为0

        $oldNum=intval($this->param["totalNum"]);
        if($oldNum && $start){
            //数据有新增时
            if($totalNum>$oldNum){
                $dif=$totalNum-$oldNum;
                $start=$start+$dif;
            }else if($totalNum<$oldNum){ //数据减少时
                $dif=$oldNum-$totalNum;
                $start=$start-$dif;
            }
        }

        $this->ret["other"]["totalNum"]=$oldNum;
        if(!$oldNum){
            $this->ret["other"]["totalNum"]=$totalNum;
        }

        return array($start,$pageSize);
    }


    //所有错误信息
    protected function errormsg($flag){
        /*
          1-1000     系统（1000：内部错误）
          1001-2000  用户
          2001-3000  订单
          3001-4000  产品
         */
        $flag=intval($flag);
        if(!$flag){
            return '俺真的错了~';
        }

        $error=array(
            1 => '参数错误',
            2 => '未知错误',
            3 => '参数解析失败',
            4 => '参数不全',
            5 => '参数type和to不存在',
            6 => '暂未获取到更多数据',
            7 => '非法请求',
            8 => '没有数据',
            9 => '操作处理失败',
            10 => '数据处理出问题了',
            11 => '登录验证失败',

            //用户
            1001=>'余额不足了哦',
            1002=>'用户不存在',
            1003=>'余额扣款失败',
            1004=>'用户成长值增加失败',
            1005=>'写入账户余额支付流水失败',

            //沙龙相关
            2001=>'项目价格规则不存在',
            2002=>'订单信息错误',
            2003=>'该项目已被商家下架或删除',
            2004=>'项目分类错误',
            2005=>'活动兑换分类不存在',
            2006=>'项目价格更新失败',
            2007=>'店铺名不存在',
            2008=>'合同编号不存在',
            2009=>'更新库存信息失败',

            //订单状态
            2201=>'订单删除失败',  //状态修改为status=20
            2202=>'支付后修改订单状态失败',
 
            //臭美卷相关
            2301=>'支付未立即成功，可能有几分钟延迟，请进入【我的臭美券】确认订单状态！',
            2302=>'臭美券信息错误',
            2303=>'臭美券状态更新失败',
            2304=>'臭美券退款数据添加失败',
            2305=>'臭美券动态表数据添加失败',
            2306=>'臭美券已使用',
            2307=>'臭美券正在退款中',
            2308=>'臭美券已退款',
            2309=>'臭美劵参与过活动,无法退款哦~',
            2310=>'您参与了优惠活动，不支持退款哟！',

            //赏金相关
            2500=>'没有找到进行中的赏金任务',
            2501=>'已评论过了',
            2502=>'该赏金任务还未打赏哦',
            2503=>'用户满意打赏时更新失败',
            2504=>'用户不满意时更新数据失败',
            2505=>'用户评价添加数据失败',
            2506=>'未找到赏金任务信息',
            2507=>'取消失败,该任务无法取消',
            2508=>'发布任务添加失败',
            2509=>'抢单状态更新失败',
            2510=>'选择造型师时订单更新失败',
            2511=>'闺蜜需求添加失败',
            2512=>'美发需求参数错误',
            2513=>'店铺流水表更新失败',
            2514=>'您还有在进行中的任务哦',
            2515=>'赏金金额不足',
            2516=>'臭美代选造型师失败',
            2517=>'用户选择造型师错误',
            2518=>'已选中用户更新失败',
            2519=>'用户未选择造型师状态更新失败',
            2520=>'您可以试试提高赏金金额或修改服务区域',
            2521=>'赏单信息查询失败',
            2522=>'未抢单未选中造型师状态更新失败',
            2523=>'所选地区暂无对应设计师请重新选择',
            2524=>'造型师所在店铺暂停服务',
            2525=>'造型师已经离职',
            2526=>'选择造型师信息不存在,请联系客服',
            
            //消息推送相关
            2601=>'消息推送数据添加失败',
            2602=>'暂无用户发布消息',
            2603=>'消息推送成功状态更新失败',
            2604=>'消息推送失败状态更新失败',
            2605=>'推送消息类型错误',
            
            //下单相关
            2901=>'购买失败，该商品已经停止销售',
            2902=>'商品库存不足了哦',
            2903=>'购买失败，您对此商品的购买次数已超出限制',
            2904=>'购买失败，您还未激活邀请码或邀请码不属于本店铺',
            2905=>'购买失败，您不符合当前项目首次消费的要求',
            2906=>'商品已下架或删除',
            2907=>'请选择退款原因',
            2908=>'该项目已售罄,请明天准时抢购',


            //下单之购物车相关
            2920=>'臭臭没有找到购物车信息哎-,-',
            2921=>'订单已支付或不存在',
            2922=>'单次只能添加10个哦',
            2923=>'购物车清除失败',
            2924=>'数量应为1-10个哦',
            2925=>'已经超过剩余库存了~',
            2926=>'购物车只能放100个哦~',
            2927=>'项目或规格不存在时，删除购物车中记录失败',
            2928=>'购物车项目数量查询有误',

            2930=>'购物车添加失败了~',
            
            
            //造型师抢单相关
            3001=>'暂无造型师抢单',
            3002=>'该任务不存在',
            3003=>'悬赏单号不存在',
            3004=>'造型师Id不存在',
            3005=>'选择造型师更新数据失败',
            3006=>'此单已经制定造型师，请退出',
            
            3007=>'您不是任务候选人',
            3008=>'该任务不由您进行服务，无权进行操作',
            3009=>'服务未完成，暂不能进行评分',
            3010=>'所选造型师手机设备类型错误,无法推送',
            3011=>'造型师不存在',
            3012=>'当前用户没有权限接赏金任务',
            
            
            //选择区域相关
            4001=>'深圳暂无此区号',
            4002=>'区信息错误',
            
            //评价相关
            5001=>'用户已评价',
            
            //限时特价项目过期信息
            6001=>'活动已过期',
            6002=>'活动未开始',


            //代金券相关
            7001=>'订单信息不存在',
            7002=>'购物车信息不存在',
            7003=>'类型错误',
            7004=>'代金券金额不足',
            7005=>'代金券不可用，请重新选择',
            7006=>'代金券绑定订单失败',
            7007=>'代金券绑定订单错误',
            7008=>'暂无可用的代金券',
            7009=>'现金券全额支付，无法退款',
            
            
            
            //内部错误
            1000 => '内部错误',
        );
        if(!isset($error[$flag])){
            return '俺真的错了~';
        }

        return $error[$flag];
    }
}
