<?php
namespace Common\Model;
use Think\Model;
class ResumeIndexModel extends model{
	protected $_validate = array(
		array('rid,type,d0','identicalNull','',1,'callback'),//不能为空
		array('rid,type,d0,d1,d2','identicalEnum','',1,'callback'),//必须为数字
	);
	/*
		添加简历索引信息
		$data[]['rid'] 简历id
		$data[]['type'] 索引类型  int array(1=>'district',2=>'jobs',3=>'trade',4=>'tag');
		$data[]['d'] 索引数据 字符串 格式为1.0.1,2.1.3
	*/
	public function add_resume_index($data)
	{
		foreach ($data as $k => $val) {
			$this->where(array('rid'=>$val['rid'],'type'=>$val['type']))->delete();
			if(!$val['d']) continue;
			foreach (explode(",",$val['d']) as $key => $value) {
				$a=explode(".",$value);
				$data_arr[]= $tmp = array('rid'=>$val['rid'],'type'=>$val['type'],'d0'=>intval($a[0]),'d1'=>intval($a[1]),'d2'=>intval($a[2]));
			}
		}
		if($data_arr && !$this->addAll($data_arr))return false;
		return true;
	}
	/*
		获取简历索引信息
		@$data['rid'] 简历id
		@$data['type'] 1=>'district',2=>'jobs',3=>'trade',4=>'tag'
		return 索引数据 字符串 格式为1.0.1,2.1.3
	*/
	public function get_resume_index($data)
	{
		$resume_index='';
		$rst = $this->where(array('rid'=>$data['rid'],'type'=>$data['type']))->limit(5)->select();
		foreach ($rst as $key => $value) 
		{
			if($value['type']==4)
			{
				$resume_index.=','.$value['d0'];
			}
			else
			{
				$resume_index.=','.$value['d0'].'.'.$value['d1'].'.'.$value['d2'];
			}
		}
		return ltrim($resume_index,',');
	}
}
?>