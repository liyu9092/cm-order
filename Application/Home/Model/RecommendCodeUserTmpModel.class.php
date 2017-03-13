<?php
/**
 * Created by PhpStorm.
 * User: cartman
 * Date: 2015/3/25
 * Time: 11:37
 */
namespace Home\Model;

/**
 * @deprecated since version thrift_150709
 */
class RecommendCodeUserTmpModel extends BaseModel
{
    /**
     * 检测用户是否已经填写过激活码了
     * @param $userId
     * @return bool
     */
    public function checkUserRecordExists($userId)
    {
        $record = $this->where('user_id = ' . $userId)->find();
        if ($record == null) {
            return false;
        }
        return true;
    }
}