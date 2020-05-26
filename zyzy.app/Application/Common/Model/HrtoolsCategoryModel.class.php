<?php 
namespace Common\Model;
use Think\Model;
class HrtoolsCategoryModel extends Model{
	protected $_validate = array(
		array('c_name','require','{%hrtools_category_null_error_c_name}',1),
		array('c_name','1,60','{%hrtools_category_length_error_c_name}',1,'length'),
		array('c_name','','{%hrtools_category_unique_c_name}',0,'unique'),
	);
	protected $_auto = array (
		array('c_adminset',0),
	);
	public function category_cache(){
		$hrtools_category = $this->order('c_order desc,c_id')->getfield('c_id,c_name');
		F('hrtools_category',$hrtools_category);
		return $hrtools_category;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('hrtools_category', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('hrtools_category', NULL);
    }
}	
?>