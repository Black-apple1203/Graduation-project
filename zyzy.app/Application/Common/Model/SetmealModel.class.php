<?php
// +----------------------------------------------------------------------
// | 74CMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.74cms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | ModelName: 套餐表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class SetmealModel extends Model{
	protected $_validate = array(
		array('display,apply,days,expense,jobs_meanwhile,refresh_jobs_free,download_resume,download_resume_max,show_order,set_sms,set_points','identicalEnum','',1,'callback'),
		//套餐名称长度验证
		array('setmeal_name','1,60','{%setmeal_setmeal_name_length_error}',1,'length'),
		//套餐说明长度验证
		array('added','0,400','{%setmeal_added_length_error}',1,'length'),
	);
    protected $_auto = array ( 
        array('show_apply_contact',0),//主动申请的简历是否可以直接查看联系方式
        array('show_contact_direct',0),//直接显示联系方式
    );

	/*
		id 获取套餐内容
	*/
	public function get_setmeal_one($id)
	{
		return $this->where(array('id'=>$id))->find();
	}	
	/**
     * 读取套餐生成缓存文件
     */
    public function setmeal_cache() {
        $res = $this->where(array('display'=>1))->order('show_order desc,id')->getField('id,setmeal_name');
        F('setmeal', $res);
        return $res;
    }
    /**
     * [get_setmeal_cache 读取缓存]
     * @param  string $id [单一套餐id]
     * @return array       [分类集]
     */
    public function get_setmeal_cache($id='')
    {
        if(false === $setmeal = F('setmeal')){
            $setmeal = $this->setmeal_cache();
        }
        if($id) return $setmeal[$id];
        return $setmeal;
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('setmeal', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('setmeal', NULL);
    }
    /**
     * 计算某一增值服务类型的最大折扣
     */
    public function get_max_discount($cat){
        switch($cat){
            case 'download_resume':
            case 'sms':
            case 'stick':
            case 'emergency':
            case 'auto_refresh_jobs':
            case 'tpl':
                $field = 'discount_'.$cat;
                break;
            default:
                $field = 'discount_download_resume';
                break;
        }
        $return = $this->where(array($field=>array('gt',0)))->min($field);
        return $this->_format_discount($return);
    }
    /**
     * 获取某一套餐id下的某一服务类型的折扣
     */
    public function get_increment_discount_by_id($cat,$setmeal_id){
        $setmeal = $this->where(array('id'=>$setmeal_id))->find();
        $return = $this->get_increment_discount_by_array($cat,$setmeal);
        return $this->_format_discount($return);
    }
    /**
     * 获取某一套餐array下的某一服务类型的折扣
     */
    public function get_increment_discount_by_array($cat,$setmeal){
        switch($cat){
            case 'download_resume':
            case 'sms':
            case 'stick':
            case 'emergency':
            case 'auto_refresh_jobs':
            case 'tpl':
                $field = 'discount_'.$cat;
                break;
            default:
                $field = 'discount_download_resume';
                break;
        }
        $return = $setmeal[$field];
        return $this->_format_discount($return);
    }
    /**
     * 获取某一套餐的所有增值服务项目中的最低折扣
     */
    public function get_discount_for_setmeal_one($setmeal){
        $arr[0] = $setmeal['discount_download_resume'];
        $arr[1] = $setmeal['discount_sms'];
        $arr[2] = $setmeal['discount_stick'];
        $arr[3] = $setmeal['discount_emergency'];
        $arr[4] = $setmeal['discount_tpl'];
        $arr[5] = $setmeal['discount_auto_refresh_jobs'];
        unset($arr[array_search(0, $arr)]);
        $pos = array_search(min($arr), $arr);
        $return = $arr[$pos];
        return $this->_format_discount($return);
    }
    protected function _format_discount($value){
        $value_arr = explode(".", $value);
        if($value_arr[1]==0){
            return $value_arr[0];
        }else{
            return $value;
        }
    }
}
?>