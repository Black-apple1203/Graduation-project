<?php 
namespace Common\Model;
use Think\Model;
class AdModel extends Model{
	protected $_validate = array(
		/*array('category_id,title,alias,img_path,content','identicalNull','',0,'callback'),
		array('category_id,type_id','identicalEnum','',0,'callback'),
		array('title','1,100','{%ad_length_error_title}',0,'length'),
		array('alias','1,80','{%ad_length_error_alias}',0,'length'),*/
	);
	protected $_auto = array (
		array('is_display',0),
		array('addtime','time',1,'function'),
		array('starttime','timestamp',3,'callback'),
		array('deadline','timedtamp',3,'callback'),
		array('oig','pc'),// 标识 是pc广告还是，手机端广告1pc 2 手机端
	);
	protected function timestamp($d){
		return $d ? strtotime($d) : time();
	}
	protected function timedtamp($d){
		return $d ? strtotime($d) : 0;
	}
	/*
		获取广告列表
		@ $where 查询条件 array
		@ $offset 显示数目  默认 10
		返回值 array
		$result  广告列表
	*/
	public function get_ad_list($where,$offset=10){
		$result = $this->where($where)->order('show_order desc')->limit($offset)->select();
		return $result;
	}
}
?>