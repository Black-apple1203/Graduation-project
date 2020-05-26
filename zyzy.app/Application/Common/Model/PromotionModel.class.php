<?php
namespace Common\Model;
use Think\Model;
class PromotionModel extends Model{
	protected $_validate = array(
		array('cp_uid,cp_jobid,cp_days,cp_starttime,cp_endtime','identicalNull','',0,'callback'),
		array('cp_uid,cp_jobid,cp_days,cp_starttime,cp_endtime','identicalEnum','',0,'callback'),
	);
	public function add_promotion($data)
	{
		if(false === $this->create($data))
		{
			return array('state'=>0,'error'=>$this->getError());
		}
		else
		{
			if(false === $insert_id = $this->add())
			{
				return array('state'=>0,'error'=>'数据添加失败！');
			}
		}
		return array('state'=>1,'id'=>$insert_id);
	}
	// 设置推广
	public function set_job_promotion($jobid,$type)
	{
		$where['id']=$jobid;
		if($type=='emergency' || $type=='stick'){
			$data[$type] = 1;
			if($type == 'stick'){
				$refreshtime = D('Jobs')->where($where)->getfield('refreshtime');
				$data['stime'] = intval($refreshtime)+100000000;
			}
			D('Jobs')->jobs_setfield($where,$data);
		}
	}
	public function format_list($list){
		foreach ($list as $key => $value) {
			$arr = $value;
			$arr['jobs_name']=cut_str($value['jobs_name'],10,0,"...");
			$arr['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$value['cp_jobid']));
			$arr['companyname']=cut_str($value['companyname'],15,0,"...");
			$arr['company_url']=url_rewrite('QS_companyshow',array('id'=>$value['company_id']));
			$list[$key] = $arr;
		}
		return $list;
	}
	/**
	 * 检查是否已推广
	 */
	public function check_promotion($jobid,$ptype){
		return $this->where(array('cp_jobid'=>array('eq',$jobid),'cp_ptype'=>array('eq',$ptype)))->find();
	}
	public function cancel_promotion($jobid,$type){
		$where['id']=array('eq',$jobid);
		if($type=='emergency' || $type=='stick'){
			$data[$type] = 0;
			if($type == 'stick'){
				$refreshtime = M('Jobs')->where($where)->getfield('refreshtime');
				!$refreshtime && $refreshtime = M('JobsTmp')->where($where)->getfield('refreshtime');
				$data['stime'] = $refreshtime;
			}
			D('Jobs')->jobs_setfield($where,$data);
		}
		return true;
	}
	/**
	 * 取消推广
	 */
	public function del_promotion($id){
		if (!is_array($id)) $id=array($id);
		$sqlin=implode(",",$id);
		$return=0;
		if (fieldRegex($sqlin,'in'))
		{
			$jobid_obj = $this->where(array('cp_id'=>array('in',$sqlin)))->select();
			foreach ($jobid_obj as $key => $value) {
				$this->cancel_promotion($value['cp_jobid'],$value['cp_ptype']);
			}
			$return = $this->where(array('cp_id'=>array('in',$sqlin)))->delete();
		}
		return $return;
	}
}
?>