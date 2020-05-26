<?php
namespace Common\Model;
use Think\Model;
class JobsSearchKeyModel extends Model{
	protected $_validate = array(
		array('uid,nature,topclass,category,trade,district,education,experience,minwage,maxwage,map_x,map_y,refreshtime,setmeal_id','identicalNull','',0,'callback'),
		array('uid,nature,topclass,category,trade,education,experience,minwage,maxwage,map_x,map_y,refreshtime,setmeal_id','identicalEnum','',0,'callback'),

	);
	protected $_auto = array ( 
		array('stick',0),//置顶
		array('emergency',0),//紧急招聘
		array('sex',3),//性别
		array('scale',0),//公司规模
	);
}
?>