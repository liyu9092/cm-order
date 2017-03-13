<?php

namespace Home\Controller;
use Think\Log;

if(!IS_CLI)
    exit("this script could only run in cli mode");

class FixFundflowDataController extends BaseController
{
    

    public function _initialize() 
    {
        # 不允许外部访问
        if(!IS_CLI)
            exit("this script could only run in cli mode");
    }
    
    /***
     * 修复数据，8月1号到9月22号，代金券+支付宝支付方式流水写入错误
     */
    public function fixScript(){
        set_time_limit(0);
        $Model = new \Think\Model();
        
        $sql = "SELECT DISTINCT
			t.record_no
		FROM
			cm_fundflow t 
                where FROM_UNIXTIME(t.add_time,'%Y%m%d') BETWEEN '20150801' and '20150922'
                and 	t.pay_type IN (2, 9) 
                and not EXISTS (select 1 from cm_fundflow t2 where t2.pay_type='4' and t.record_no=t2.record_no) 
		GROUP BY
			t.record_no,t.money
		HAVING
			count(DISTINCT t.pay_type) = 2 ";
        $res = $Model->query($sql);  
        //print_r($res);exit;
        foreach ($res as $key => $value) {
            $orderSn = $value['record_no'];
            $sql2 = "
                    SELECT
                            priceall,
                            actuallyPay,
                            (priceall - actuallyPay) AS voucherPay
                    FROM
                            cm_order
                    WHERE
                            ordersn = '{$orderSn}' and ispay = 2";
            $res2 = $Model->query($sql2);    
            //print_r($res2);exit;
            /**
             * Array
                    (
                        [0] => Array
                            (
                                [priceall] => 2.00
                                [actuallyPay] => 1.00
                                [voucherPay] => 1.00
                            )

                    )
                    Array
                    (
                        [0] => Array
                            (
                                [priceall] => 54.00
                                [actuallyPay] => 24.00
                                [voucherPay] => 30.00
                            )

                    )
             */
            //更新数据
            $sql3 = "
                    UPDATE cm_fundflow t
                    SET t.money = {$res2[0]['voucherPay']}
                    WHERE
                            t.record_no = '{$orderSn}'
                    AND t.pay_type = 9 ";
            $sql4 = "
                    UPDATE cm_fundflow t
                    SET t.money = {$res2[0]['actuallyPay']}
                    WHERE
                            t.record_no = '{$orderSn}'
                    AND t.pay_type = 2 ";
            $Model->query($sql3); 
            $Model->query($sql4);
            
        }
        echo "success";
        exit;
             
        //print_r($res);
        /**
        * Array
       (
           [0] => Array
               (
                   [record_no] => 3956452311208
               )

           [1] => Array
               (
                   [record_no] => 3963914011339
               )

           [2] => Array
               (
                   [record_no] => 3970262311423
               )

           [3] => Array
               (
                   [record_no] => 3997805011398
               )

           [4] => Array
               (
                   [record_no] => 3997920611836
               )

           [5] => Array
               (
                   [record_no] => 3997945311282
               )
        )
         */
    }


}
