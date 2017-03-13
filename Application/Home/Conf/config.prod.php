<?php

return array(
    //'配置项'=>'配置值'
    'DB_TYPE' => 'mysqli', // 数据库类型
    'DB_HOST' => '<#DB_HOST#>', // 数据库服务器地址
    'DB_NAME' => '<#DB_NAME#>', // 数据库名
    'DB_USER' => '<#DB_USER#>', // 数据库用户名
    'DB_PWD' => '<#DB_PWD#>', // 数据库密码
    'DB_PORT' => '<#DB_PORT#>', // 数据库端口
    'DB_PREFIX' => 'cm_', // 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
    'DB_CHARSET' => 'utf8', // 数据库编码
    'MODULE_ALLOW_LIST' => array('Home', 'Merchant'),
    'DEFAULT_MODULE' => 'Home',
    //额外的参数
    'PHONE_MSG_URL' => '<#SMS_URL#>/sms/send/?', //发送短信的地址配置
    'PUSH_MESSAGE_ANDROID_URL'  => '<#SERVICE_URL#>/push/android',  //android推送地址
    'PUSH_MESSAGE_IOS_URL'  => '<#SERVICE_URL#>/push/ios',  //ios推送地址

    'OTHER_URL' => array(
        'COUPON_SHAREURL'  => '<#M_URL#>/choumeiVIP/module/userAccount/sharePromoCode.html',
        'WEIXIN_PACKETURL' => '<#TRADE_URL#>/wechat/Wxpacket/WPublic', //微信红包的地址
        'SENDORDER_URL'    => '<#CRM_URL#>/Inside/sendOrder', //crm同步订单
        'WECHAT_REDPACKET'=>'<#WECHAT_URL#>/bonus.php?'//发微信红包地址
    ),
    'MANAGER_URL'=>'<#MANAGER_BACK_URL#>/shop_count/count_order',//新管理后台
    'PUSH_SERVICE_ANDROID' => '<#SERVICE_URL#>/push/android',
    'PUSH_SERVICE_IOS'     => '<#SERVICE_URL#>/push/ios',
    'THRIFT_SERVER_IP'      => '<#THRIFT_SERVER_IP#>',
    'THRIFT_SERVER_PORT'    => '<#THRIFT_SERVER_PORT#>',
);
