<?php
namespace Common\Model;
use Think\Model;
class TplModel extends Model{
	protected $_validate = array(
		array('tpl_type,tpl_name,tpl_dir','identicalNull','',1,'callback'),
		array('tpl_name','80','{%tpl_length_error_tpl_name}',0,'length'),

	);
	protected $_auto = array ( 
		array('tpl_display',1),
		array('tpl_val',1),
	);
	/**
	 * [resume_tpl_cache description]
	 */
	public function resume_tpl_cache(){
		$resume_tpl = $this->where(array('tpl_display'=>1))->getfield('tpl_dir,tpl_name');
		F('resume_tpl',$resume_tpl);
		return $resume_tpl;
	}
	/**
	 * [resume_tpl 简历切换模板]
	 */
	public function resume_tpl($tpl,$user){
		$id = M('ResumeTpl')->where(array('tpl'=>$tpl))->getfield('id');
		if(!$id) return array('state'=>0,'error'=>'模板切换失败，您还没有购买此模板！');
		$reg = M('Resume')->where(array('pid'=>$pid,'uid'=>$user['uid']))->setfield('tpl',$tpl);
		if(false === $reg) return array('state'=>0,'error'=>'模板切换失败，请重新操作！');
		return array('state'=>1);
	}
	/**
	 * [set_tpl 切换模版]
	 */
	public function set_tpl($tpl_one,$user){
		$members_points_mod = D('MembersPoints');
		$user_points = $members_points_mod->get_user_points($user['uid']);
		$setmeal = D('MembersSetmeal')->get_user_setmeal($user['uid']);
		if ($setmeal['endtime']<time() && $setmeal['endtime']<>"0") return array('state'=>0,'error'=>"您的服务已经到期，请重新开通！",'url'=>'setmeal_order_add');
    	M('CompanyProfile')->where(array('uid'=>$user['uid']))->setField('tpl',$tpl_one['tpl_name']);
    	M('Jobs')->where(array('uid'=>$user['uid']))->setField('tpl',$tpl_one['tpl_name']);
    	M('JobsTmp')->where(array('uid'=>$user['uid']))->setField('tpl',$tpl_one['tpl_name']);
		return array('state'=>1);
	}
}
?>