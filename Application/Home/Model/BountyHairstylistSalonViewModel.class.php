<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

use Think\Model\ViewModel;

class BountyHairstylistSalonViewModel extends ViewModel {

    public $viewFields = array(
        'bounty_task' => array(
            'btId',
            'btSn'=>'bountySn',
            'money',
            'needsStr',
            'name',
            'madeTo',
            'reason',
            'remark',
            'selectType',
            'satisfyType',
            'isPay',
            'btStatus',
            'requestNum',
            'userScore',
            'hairstylistScore',
            'addTime',
            'selectTime',
            'serviceTime',
            'endTime',
            'taskType',
            '_type' => ' LEFT'
        ),
        'hairstylist' => array(
            'stylistId',
            'stylistName',
            'stylistImg',
            'job',
            'mobilephone',
            '_on' => 'hairstylist.stylistId=bounty_task.hairstylistId',
            '_type' => ' LEFT'
        ),
        'salon' => array(
            'salonid' => 'salonId',
            'salonname' => 'salonName',
            'addrlati' => 'addrLati',
            'addrlong' => 'addrLong',
            'addr',
            'district ',
            'zone ',
            '_on' => 'hairstylist.salonid = salon.salonid

    '
        ),
    );

}
