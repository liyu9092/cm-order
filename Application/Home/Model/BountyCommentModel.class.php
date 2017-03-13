<?php

namespace Home\Model;
use Think\Model;

class BountyCommentModel extends BaseModel {

    // protected $this->tableName = 'bounty_comment';
    /*     * *
     * 用户针对已完成的赏金任务添加评价信息
     * @param $data 数组数据
     * @lufangrui
     * @2015-05-16
     * * */
    public function addComment($data) {
        $thrift = D('ThriftHelper');
        /*
          $return = $thrift->request('comment-center', 'addBtComment', array( $userId,$btSn,$hairstylistId,$type,$imgSrc,$notSatisfyStr,$addTime,$content));
         */
        $param = new \cn\choumei\thriftserver\service\stub\gen\AddBtCommentParam();
        $param->addTime = $data['addTime'];
        $param->btSn = $data['btSn'];
        $param->content = $data['content'];
        $param->hairstylistId = $data['hairstylistId'];
        $param->imgSrc = $data['imgSrc'];
        $param->notSatisfyStr = $data['notSatisfyStr'];
        $param->type = $data['type'];
        $param->userId = $data['userId'];
        $return = $thrift->request('comment-center', 'addBtComment', array($param));
        return $return;
    }

    /*     * *
     * 获取用户针对某赏金单号是否评论过
     * @param $where 条件
     * @lufangrui
     * @2015-05-16
     * * */

    public function commentExists($where) {
        return $this->where($where)->count();
    }

    /*     * *
     * 获取用户评论赏金信息
     * @param $btSn 赏金单号
     * @param $userId 用户id
     * @lufangrui
     * @2015-05-16
     * * */

    public function getComment($btSn, $userId) {
    	/*
    	$bountyCommentModel = D('bounty_comment');
        $fields = array(
            'content','imgSrc','addTime'
        );
        // 评论类型 1:用户打赏不满意信息 2:用户评论
        $where = ' btSn=%s and userId=%s and type = 2 ';
        $condition = array( $btSn,$userId );
        $return = $bountyCommentModel->field( $fields )->where( $where,$condition )->find();
        return $return;
        */
    	
        $comment = $this->getCommentBySn($btSn, 2);
        if (empty($comment) || $comment['userId'] != $userId)
            return null;
        return array('content' => $comment['content'], 'imgSrc' => $comment['imgSrc'], 'addTime' => $comment['addTime']);
    }

    public function getCommentBySn($btSn, $type) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('comment-center', 'getBountyCommentBySn', array($btSn, $type));
        return $return;
    }

    /**
     * 通过造型师id找到用户评论的信息
     */
    public function getBtCommentByStylistId($stylistId, $page, $pageSize) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('comment-center', 'getBtCommentByStylistId', array($stylistId, $page, $pageSize));
        return $return;
    }

    /*
     * 通过造型师id拿到评价的数量
     */

    public function getBtCommentCountByStylistId($stylistId) {
        $thrift = D('ThriftHelper');
        $return = $thrift->request('comment-center', 'getBtCommentCountByStylistId', array($stylistId));
        return $return;
    }

}

?>
