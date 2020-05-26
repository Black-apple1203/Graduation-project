<?php 
namespace Common\Model;
use Think\Model;
class HelpCategoryModel extends Model{
	protected $_validate = array(
		array('categoryname','require','{%help_category_null_error_categoryname}',1),
		array('categoryname','1,60','{%help_category_length_error_categoryname}',1,'length'),
	);
	public function category_cache(){
		$data = $this->order('category_order desc,id')->getfield('id,parentid,categoryname');
		foreach ($data as $key => $val) {
			$help_category[$val['parentid']][$val['id']] = $val;
		}
		F('help_category',$help_category);
		return $help_category;
	}
	public function help_category_list(){
		$help_category = $this->order('category_order desc,id')->getfield('id,parentid,categoryname');
		F('help_category_list',$help_category);
		return $help_category;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('help_category', NULL);
        F('help_category_list',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
    	$options['where']['id'][1] && $this->where(array('parentid'=>array('in',$options['where']['id'][1])))->delete();
        F('help_category', NULL);
        F('help_category_list',NULL);
    }
}	
?>