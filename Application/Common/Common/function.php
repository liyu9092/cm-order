<?php
function topictime($time) {

    $lefttime = time() - $time;

    if($lefttime > 0) {
        if($lefttime < 3600) {
            $word = round($lefttime/60).'分钟前';
        } else if($lefttime < 86400) {
            $word = round($lefttime/3600).'小时前';
        } else {
            $word = date('Y-m-d H:i:s');
        }
    } else {
        $word = '刚刚';
    }

    return $word;

}

function curlPost($url,$data) {

    // 1. 初始化
    $ch = curl_init();
    // 2. 设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    //curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1); //设置不用等待

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    // 3. 执行并获取HTML文档内容
    $output = curl_exec($ch);
    //var_dump($output);exit;
    if ($output === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
    }
    // 4. 释放curl句柄
    curl_close($ch);
    return $output;

}

function curlPostAjax($url,$data) {

    // 1. 初始化
    $ch = curl_init();
    // 2. 设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    //curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_NOSIGNAL, true); 
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 800); //设置不用等待

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    // 3. 执行并获取HTML文档内容
    $output = curl_exec($ch);
    //var_dump($output);exit;
    if ($output === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
    }
    // 4. 释放curl句柄
    curl_close($ch);
    return $output;

}

function curlGet($url,$data) {

	$url  = $url. http_build_query( $data );

    // 1. 初始化
    $ch = curl_init();
    // 2. 设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    //curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    //curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1); //设置不用等待
    
    // 3. 执行并获取HTML文档内容
    $output = curl_exec($ch);
    //var_dump($output);exit;
    if ($output === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
    }
    // 4. 释放curl句柄
    curl_close($ch);
    return $output;

}

function curlGetAjax($url,$data) {
 
	$url  = $url. http_build_query( $data );
    //var_dump($url);exit;

    // 1. 初始化
    $ch = curl_init();
    // 2. 设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    //curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_NOSIGNAL, true); 
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 800); //设置不用等待
    
    // 3. 执行并获取HTML文档内容
    $output = curl_exec($ch);
    //var_dump($output);exit;
    if ($output === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
    }
    // 4. 释放curl句柄
    curl_close($ch);
    return $output;

}

//sock模拟post提交
function sock_post($url, $data) {  
    $query_str = http_build_query($data);
    $info = parse_url($url);

    $fp = fsockopen($info["host"], 80, $errno, $errstr, 3);
    if(!$fp) {
        file_put_contents("logs/".date('Y-m-d').'_tongbunotcon.log',$query_str."\r\n",FILE_APPEND);
        return false;
    }
    $head = "POST ".$info['path']."?".$info["query"]." HTTP/1.0\r\n";
    $head .= "Host: ".$info['host']."\r\n";
    $head .= "Referer: http://".$info['host'].$info['path']."\r\n";
    $head .= "Content-type: application/x-www-form-urlencoded\r\n";
    $head .= "Content-Length: ".strlen(trim($query_str))."\r\n";
    $head .= "\r\n";
    $head .= trim($query_str);
    $write = fputs($fp, $head);
    if(!$write) {
        file_put_contents("logs/".date('Y-m-d').'_tongbufail.log',$query_str."\r\n",FILE_APPEND);
        return false;
    }
    //while (!feof($fp))
    //{
    //    $line = fread($fp,4096);
    //    echo $line;
    //}
    fclose($fp);
    return true;
}

//sock模拟get提交
function sock_get($url, $data) {
    $query_str = http_build_query($data);
    $info = parse_url($url);

    //if($query_str == $last_query_str) return; //如果一样的短信，就不发了
    if(strcasecmp($query_str, $last_query_str) == 0) return;

    $fp = fsockopen($info["host"], 80, $errno, $errstr, 3);
    if(!$fp) {
        file_put_contents("logs/".date('Y-m-d').'_messagenotcon.log',$query_str."\r\n",FILE_APPEND);
        return false;
    }
    $head = "GET ".$info['path']."?".$query_str." HTTP/1.1\r\n";
    $head .= "Host: ".$info['host']."\r\n";
    $head .= "\r\n";
    $write = fputs($fp, $head);
    if(!$write) {
        file_put_contents("logs/".date('Y-m-d').'_messagefail.log',$query_str."\r\n",FILE_APPEND);
        return false;
    }
    //while (!feof($fp)) {
    //    $line = fread($fp,4096);
    //    echo $line;
    //}
    fclose($fp);
    return true;
}


//获取验证码
function getauthcode() {
    $code = '';
    for($i=0;$i<6;$i++) {
        $code .= rand(0,9);
    }
    return $code;
}


//发送短信
function sendphonemsg($mobilephone,$smstxt) {
    $url = C('PHONE_MSG_URL');

    $data = array('phone'=>$mobilephone,'smstxt'=>$smstxt);
    $codeVal=http_build_query($data);

    $DesObj=D('Des');;
    //加密参数
    $desStr=$DesObj->encrypt($codeVal);

    $param['code']=$desStr;
    try{
        $result = curlGet($url, $param);
    }catch (Exception $e){
        $result=$e->getMessage();
    }
    //发送日志
    \Think\Log::write('SMS-Send: Rs=>' . $result . ';Mobile=>' . $mobilephone . ';Info=>' . $smstxt, 'LSP');
}


//获取设备类型(1:ios;2:android;3:微信)
function getSourcetype($useragent='') {
    if(!$useragent){
        $useragent  = strtolower($_SERVER["HTTP_USER_AGENT"]);
    }

    //$type = 3;//默认微信(web浏览器)
	$type = 0;
    if(strripos($useragent, 'micromessenger')) {
        $type = 3;
    }else if(strripos($useragent,'iphone') || strripos($useragent,'ipad') || strripos($useragent,'ipod')) {
        $type = 1;
    } else if(strripos($useragent,'android')) {
        $type = 2;
    }

    return $type;
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false) {
    if(function_exists("mb_substr")){
        if($suffix)
            return mb_substr($str, $start, $length, $charset)."...";
        else
            return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
        if($suffix)
            return iconv_substr($str,$start,$length,$charset)."...";
        else
            return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

/**
 * 发布消息展示使用到此时间 秒，分钟，小时，目前没使用到天的情况
 * @param type $btime
 * @return string
 */
function times($btime){  
    $result = '';  
    $time =time() - $btime ;  
    if($time < 60)  
    {  
        $result = $time.'秒前';  
    }  
    else if($time < 1800)  
    {  
        $result = floor($time/60).'分钟前';  
    }  
    else if($time < 3600)  
    {  
        $result = '半小时前';  
    }  
    else if($time < 86400)  
    {  
        $result = floor($time/3600).'小时前';  
    }  
    else  
    {  
        $zt = strtotime(date('Y-m-d 00:00:00'));  
        $qt = strtotime(date('Y-m-d 00:00:00',strtotime("-1 day")));  
        $st = strtotime(date('Y-m-d 00:00:00',strtotime("-2 day")));  
        $bt = strtotime(date('Y-m-d 00:00:00',strtotime("-7 day")));  
        if( $btime < $bt)  
        {  
            $result = date('Y-m-d H:i:s', $btime);  
        }  
        else if($btime < $st)  
        {  
            $result = floor($time/86400).'天前';  
        }  
        else if($btime < $qt)  
        {  
            $result = "前天".date('H:i', $btime);  
        }  
        else  
        {  
            $result = '昨天'.date('H:i', $btime);  
        }  
    }  
    return $result;  
}  