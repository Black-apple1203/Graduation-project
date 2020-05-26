<?php 
namespace Common\Model;
use Think\Model;
class ExplainCategoryModel extends Model
{
	protected $_validate = array(
		array('categoryname','require','{%explain_category_null_error_categoryname}',1),
		array('categoryname','1,60','{%explain_category_length_error_categoryname}',1,'length'),
		array('categoryname','','{%explain_category_unique_category}',0,'unique'),
	);
	protected $_auto = array (
		array('admin_set',0),
	);
	public function category_cache(){
		$explain_category = $this->order('category_order desc,id')->getfield('id,categoryname');
		F('explain_category',$explain_category);
		return $explain_category;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('explain_category', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('explain_category', NULL);
    }
}	
?>