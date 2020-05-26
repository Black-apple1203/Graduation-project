<?php
namespace Common\Model;
use Think\Model;
class NavigationCategoryModel extends Model{
	protected $_validate = array(
		//array('alias,categoryname,','identicalNull','',1,'callback'),
	);
	protected $_auto = array ( 
		array('admin_set',0),
	);
	public function nav_categroy_cache(){
		$nav_categroy = $this->getfield('alias,categoryname');
		F('nav_categroy',$nav_categroy);
		return $nav_categroy;
	}
}
?>