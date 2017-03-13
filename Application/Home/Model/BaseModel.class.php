<?php
/**
 * model基类
 * @author carson
 */
namespace Home\Model;
use Think\Model;

class BaseModel extends Model{

    public function getInfo($where,$field='*'){
        /*
        return $this->where($where)->field($field)->find();
         */
    }


    public function getInfoById($id,$field='*'){
        $where[$this->pk]=$id;

        return $this->getInfo($where,$field);
    }


    protected function show_log($info,$tag='NEWAPI'){
        \Think\Log::write($info,$tag);
    }

}
