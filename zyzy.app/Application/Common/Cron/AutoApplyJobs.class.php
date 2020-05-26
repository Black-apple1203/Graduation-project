<?php
/*
* 74cms 计划任务 委托简历自动申请职位
* ============================================================================
* 版权所有: 骑士网络，并保留所有权利。
* 网站地址: http://www.74cms.com；
* ----------------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ============================================================================
*/
defined('THINK_PATH') or exit();
ignore_user_abort(true);
class AutoApplyJobs{
	public function run(){
		$time = time();
		M('ResumeEntrust')->where(array('entrust_end'=>array('lt',$time)))->delete();
		$rids = M('ResumeEntrust')->where(array('entrust_start'=>array('lt',$time),'entrust_end'=>array('gt',$time)))->getfield('resume_id',true);
		if(!$rids) return false;
		$resume = M('Resume')->where(array('id'=>array('in',$rids)))->getfield('id,uid,district,intention_jobs_id');
		$resume_work = M('ResumeWork')->where(array('pid'=>array('in',$rids)))->getfield('id,pid,companyname');
		foreach ($resume_work as $key => $val) {
			$resume_work[$val['pid']][$val['id']] = $val['companyname'];
		}
		foreach ($resume as $key => $val) {
			$map[] = $this->_parse('city',$val['district']);
			$map[] = $this->_parse('jobs',$val['intention_jobs_id']);
			$map[] = '+ent1';
			if(C('qscms_jobs_display')==1){
				$map[] = '+audit1';
			}
			$where['key'] = array('match_mode',$map);
			$jids = M('JobsSearch')->where($where)->getfield('id');
			if(!$jids){
				continue;
			}else{
				$jobs_list = M('Jobs')->where(array('id'=>array('in',$jids)))->select();
				$data = array();
				$shield_company = M('PersonalShieldCompany')->where(array('uid'=>$val['uid']))->getfield('comkeyword',true);
				foreach ($jobs_list as $key => $_val) {
					if(M('PersonalJobsApply')->where(array('personal_uid'=>$val['uid'],'jobs_id'=>$_val['id'],'resume_id'=>$val['id']))->find()) continue;
					foreach ($shield_company as $v) {
						if(false !== strstr($v,$_val['companyname'])){
							$s = 1;
							break;
						}
					}
					if($s) continue;
					foreach ($resume_work[$val['id']] as $v) {
						if(false !== strstr($v,$_val['companyname'])){
							$s = 1;
							break;
						}
					}
					if($s) continue;
					$addarr['resume_id']=$val['id'];
					$addarr['resume_name']=$val['fullname'];
					$addarr['personal_uid']=$val['uid'];
					$addarr['jobs_id']=$_val['id'];
					$addarr['jobs_name']=$_val['jobs_name'];
					$addarr['company_id']=$_val['company_id'];
					$addarr['company_name']=$_val['companyname'];
					$addarr['company_uid']=$_val['uid'];
					$addarr['notes']= "委托投递";
					$addarr['is_apply']=1;
					$addarr['apply_addtime']=$time;
					$addarr['personal_look']=1;
					$data[] = $addarr;
					if(count($data) >= 3) break;
				}
				M('PersonalJobsApply')->addAll($data);
			}
		}
	}
	protected function _parse($k,$str){
        if($str){
        	if(is_array($str)){
        		$arr = array_slice($str,0,20);
        	}else{
        		$arr = explode(',',$str);
        		$arr = array_slice($arr,0,10);
        	}
            foreach($arr as $v) {
                $a = explode('.',$v);
                $t = range(0,5);
                foreach ($t as $key => $val) {
                	$t[$key] = isset($a[$key]) ? intval($a[$key]) : 0;
                }
		    	for($i = 5;$i>=0;$i--){
		    		$d[] = $k.implode('_',$t);
		    		unset($t[$i]);
		    	}
            }
            return $d ? '+('.implode(' ',array_unique($d)).')' : false;
        }
        return false;
    }
}
?>