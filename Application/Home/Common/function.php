<?php
use Think\Log;


function show($array){
    echo '<pre>';
    print_r($array);
    die;
}

function debug($var){
    echo '<div style="display:none">';
    print_r($var);
    echo '</div>';
}


?>
