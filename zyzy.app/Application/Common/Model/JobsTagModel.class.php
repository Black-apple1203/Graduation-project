<?php
namespace Common\Model;
use Think\Model;
class JobsTagModel extends Model{
	protected $_validate = array(
		array('uid,pid,tag','identicalNull','',0,'callback'),
		array('uid,pid,tag','identicalEnum','',0,'callback'),

	);
	protected $_auto = array ( 

	);
	/*
		添加职位标签
		@$pid 职位id int
		@$uid 企业uid int
		@$str 职位标签  str ,分割
	*/
	public function add_jobs_tag($pid,$uid,$str)
	{
		$this->where(array('pid'=>$pid,'uid'=>$uid))->delete();
		$str=trim($str);
		$arr=explode(",",$str);
		if (is_array($arr) && !empty($arr)){
			foreach($arr as $k=>$a){
				$setsqlarr[]=array('uid'=>$uid,'pid'=>$pid,'tag'=>$a);
			}
			if(!$this->addAll($setsqlarr)) return false;
		}
		return true;
	}

}
?>