<?php
namespace Common\Model;
use Think\Model;
class NoticeCategoryModel extends Model{
	protected $_validate = array(
		array('categoryname','require','{%notice_category_null_error_categoryname}',1),
		array('categoryname','1,60','{%notice_category_length_error_categoryname}',1,'length'),
		array('categoryname','','{%notice_category_unique_category}',0,'unique'),
	);
	protected $_auto = array (
		array('admin_set',0),
	);
	public function category_cache(){
		$notice_category = $this->order('sort desc,id')->getfield('id,categoryname');
		F('notice_category',$notice_category);
		return $notice_category;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('notice_category', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('notice_category', NULL);
    }
}
?>