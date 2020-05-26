<?php
namespace Common\Model;
use Think\Model;
class LinkCategoryModel extends Model{
	protected $_validate = array(
		array('categoryname,c_alias','identicalNull','',0,'callback'),
		array('categoryname','1,80','{%link_category_length_error_categoryname}',0,'length'), // 友链属性名称
		array('c_alias','1,30','{%link_category_length_error_c_alias}',0,'length'), // 友情链接调用名称

	);
	protected $_auto = array ( 
		array('c_sys',0),//友链属性是否为系统设置
	);	
}
?>