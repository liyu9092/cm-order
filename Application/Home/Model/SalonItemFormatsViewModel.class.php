<?php
/**
 * 规格分类-规格 视图
 * @author carson
 */
namespace Home\Model;
use Think\Model\ViewModel;

class SalonItemFormatsViewModel extends ViewModel {
	
	public $viewFields = array(
        'salon_item_formats' => array('salon_item_formats_id','formats_name'),
		'salon_item_format' => array('salon_item_format_id','format_name','_on' => 'salon_item_format.salon_item_formats_id=salon_item_formats.salon_item_formats_id')
	);


    public function getFormatStr($idStr){
        return $this->getFormatStrThrift($idStr);
        
        /*
        $remark='无规格';

        if(!$idStr){
            return $remark;
        }

        $where['salon_item_format_id']=array('in',$idStr);
        $pricecate = $this->where($where)->select(); //项目规则
        //print_r($pricecate);exit;

        //返回的是二维数组，进行拼接
        if(!empty($pricecate)){
            $listExtra = '';
            foreach($pricecate as $k => $v){
                $childName = $v['format_name'];
                $parentName = $v["formats_name"];
                $listExtra .= $parentName . ":" .$childName;
                if($k < count($pricecate)-1){
                    $listExtra .=";";
                }
            }
            $remark = !$listExtra? "无规格":$listExtra;
        }
        //print_r($remark);
        //die();
        return $remark;
         */
    }
    
    protected function getFormatStrThrift($formatIds)
    {
        if(!is_array($formatIds))
            $formatIds = explode(',', $formatIds);
        
        foreach($formatIds as $k => $id)
        {
            $id = intval($id);
            if($id <= 0)
                unset($formatIds[$k]);
        }
        if(empty($formatIds))
            return '无规格';
        
        $thrift = D('ThriftHelper');
        $formats = $thrift->request('seller-center', 'getFormats', array(array_unique($formatIds)));
        $typeids = array();
        foreach($formats as $format)
        {
            $typeids[] = $format['salonItemFormats'];
        }
        $types = $thrift->request('seller-center', 'getFormatTypes', array(array_unique($typeids)));
        $tmp = array();
        foreach($types as $type)
            foreach($formats as $format)
                if($format['salonItemFormats'] == $type['salonItemFormatsId'])
                    $tmp[] = "{$type['formatsName']}:{$format['formatName']}";
        $ret = implode(';', $tmp) ?: '无规格';
        return $ret;
    }
}
