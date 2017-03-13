<?php
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 定义应用目录
define('APP_PATH','./Application/');

// 部署环境 dev/test/uat/prod
//define('ENVIRONMENT','<#ENVIRONMENT#>');
define('ENVIRONMENT','workstation');

//定义默认模块
define('BIND_MODULE','Home');

// 引入ThinkPHP入口文件
require './Core/ThinkPHP.php';
// 亲^_^ 后面不需要任何代码了 就是如此简单
