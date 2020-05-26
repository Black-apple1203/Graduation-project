<?php
namespace Common\Model;

use Think\Model;

class SetmealIncrementModel extends Model
{
    public $cate_arr = array('download_resume' => '简历增值包', 'sms' => '短信增值包', 'stick' => '职位置顶', 'emergency' => '职位紧急', 'tpl' => '企业模板', 'auto_refresh_jobs' => '职位智能刷新');
    public $service_unit = array('download_resume' => '点', 'sms' => '条', 'stick' => '天', 'emergency' => '天', 'auto_refresh_jobs' => '次');

    protected $_validate = array(
        array('cat,name,value', 'identicalNull', '', 0, 'callback'),
        array('value', 'identicalEnum', '', 0, 'callback'),
    );

    /**
     * 生成缓存文件
     */
    public function set_cache()
    {
        $setmeal_increment = $this->where()->order('sort desc')->getField('id,cat,name,value,price');
        F('setmeal_increment', $setmeal_increment);
        return $setmeal_increment;
    }

    /**
     * 获取缓存
     */
    public function get_cache($cat = '', $id = 0)
    {
        if (false === F('setmeal_increment')) {
            $cache = $this->set_cache();
        } else {
            $cache = F('setmeal_increment');
        }
        if ($cat <> '') {
            $return = array();
            foreach ($cache as $key => $value) {
                if ($value['cat'] == $cat) {
                    $return[] = $value;
                }
            }
        } else {
            $return = $cache;
        }

        if ($id > 0) {
            return $return[$id];
        } else {
            return $return;
        }
    }

    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options)
    {
        F('setmeal_increment', NULL);
    }

    /**
     * 获取单条
     */
    public function getone($id)
    {
        return $this->where(array('id' => $id))->find();
    }
}

?>