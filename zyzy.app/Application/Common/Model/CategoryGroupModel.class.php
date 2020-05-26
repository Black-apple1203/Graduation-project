<?php 
namespace Common\Model;
use Think\Model;
class CategoryGroupModel extends Model
{
	protected $_validate = array(
		array('g_name,g_alias,','identicalNull','',0,'callback'),
		array('g_name','1,50','{%category_group_length_error_g_name}',0,'length'),
		array('g_alias','1,30','{%category_group_length_error_g_alias}',0,'length'),
	);
	protected $_auto = array (
		array('g_sys',0),
	);
}
?>