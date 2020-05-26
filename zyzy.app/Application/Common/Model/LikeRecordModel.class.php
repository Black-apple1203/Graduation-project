<?php
namespace Common\Model;

use Think\Model;

/**
 * 点赞记录模型
 */
class LikeRecordModel extends Model {

    /**
     * ptype字段枚举数组
     * @var array
     */
    public $type = array('1' => 'Jobs', '2' => 'ParttimeJobs', '3' => 'StorerecruitJobs', '4' => 'Resume', '5' => 'GworkerJobs');

    /* 模型自动验证 */
    protected $_validate = array(
        array('pid', 'require', 'pid不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('add_time', NOW_TIME, self::MODEL_INSERT)
    );

    /**
     * 添加点赞记录
     * @param array $data
     * @return boolean 添加状态
     */
    public function add_like($data) {
        $data = $this->create($data);
        if (!$data) { //数据对象创建错误
            return false;
        }
        /* 添加数据 */
        $r = $this->add($data);
        $r && M($this->type[$data['ptype']])->where(array('id' => $data['pid']))->setInc('like_num', 1);
        return $r;
    }

    /**
     * 取消点赞
     * @param array $where
     * @return boolean 添加状态
     */
    public function cancel_like($where) {
        /* 删除数据 */
        $r = $this->where($where)->delete();
        $r && M($this->type[$where['ptype']])->where(array('id' => $where['pid']))->setDec('like_num', 1);
        return $r;
    }
}
