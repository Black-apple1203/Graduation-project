<?php
namespace Common\Model;
use Think\Model;
class MembersHandselModel extends Model{
	protected $_validate = array(
		array('uid,htype_cn,points,addtime,operate','identicalNull','',0,'callback'),
		array('uid,points,addtime,operate','identicalEnum','',0,'callback'),
		array('htype_cn','0,60','{%members_handsel_length_error_htype}',0,'length'),
	);
	/*
		查询 会员 当天是否有某个操作
		例如 会员是否是当天第一次登录
		@$data  array('uid'=>1,'htype'=>'userlogin') 
	*/
	public function check_members_handsel_day($data){
		$data['addtime'] = array('gt',strtotime('today'));
		return $this->where($data)->find();
	}
	/*
		查询当天某个 操作 一共获得多少积分
		例如 个人 当天刷新简历一共获得多少积分
		@$data  array('uid'=>1,'htype'=>'refreshresume') 
	*/
	public function check_members_handsel_day_points($data)
	{
		$time=time();
		$today=mktime(0, 0, 0,date('m'), date('d'), date('Y'));
		$data['addtime'] = array('gt',$today);
		return $this->where($data)->sum('points');
	}
	/*
	 * 传入 数组  $data
	 */
	public function members_handsel_add($data){
		if(false === $this->create($data)) return array('state'=>0,'error'=>$this->getError());
		if(false === $insert_id = $this->add()) return array('state'=>0,'error'=>'添加数据失败！');
		return array('state'=>1,'id'=>$insert_id);
	}
	/*
		积分使用情况列表
		@$data 查询条件
	*/
	public function get_handsel_list($data,$pagesize=10)
	{
		$count = $this->where($data)->count();
		$pager =  pager($count, $pagesize);
		$rst['list'] = $this->where($data)->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
		$rst['page']=$pager->fshow();
		return $rst;
	}
}
?>