<?php
namespace Common\Model;
use Think\Model;
class RefreshLogModel extends Model{
	protected $_validate = array(
		array('uid,type','identicalNull','',0,'callback'),
		array('uid,type','identicalEnum','',0,'callback'),
	);
	protected $_auto = array ( 
		array('addtime','time',1,'function'),
	);

	/*
		获取最近一次刷新 时间
		@data  array
		@data['uid']  会员uid
		@data['type'] 
		@data['mode']

		返回时间戳
	*/
	public function get_last_refresh_date($data)
	{
		return $this->where($data)->Max('addtime');
	}
	/*
		获取当天刷新次数
		@data  array
		@data['uid']  会员uid
		@data['type'] 
		@data['mode']

		返回 次数 str
	*/
	public function get_today_refresh_times($data)
	{
		$today = strtotime(date('Y-m-d'));
		$tomorrow = $today+3600*24;
		$data['addtime'] = array(array('gt',$today),array('lt',$tomorrow),'and');
		return $this->where($data)->count();
	}
}
?>