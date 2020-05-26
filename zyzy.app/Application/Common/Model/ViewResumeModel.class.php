<?php
namespace Common\Model;
use Think\Model;
class ViewResumeModel extends Model{
	protected $_validate = array(
		array('uid,jobsid','identicalNull','',0,'callback'),
		array('uid,jobsid','identicalEnum','',0,'callback'),
	);
	protected $_auto = array ( 
		array('addtime','time',3,'function'), //查看时间 
	);
	/*
		个人 谁在关注我
		@$data array 查询条件 为 view_resume 表中条件
		$data['resumeid']=>resumeid;
		$data['addtime']=array('gt',$time);
		@$pagesiz 每页显示条数 
		返回值 数组
		$rst['list'] 列表数据 array
		$rst['page'] 分页
	*/
	public function get_view_resume($data,$pagesize=10){
		$count = $this->where($data)->count();
		$pager =  pager($count, $pagesize);
		$rst['list'] = $this->where($data)->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
		if($rst['list']){
			foreach($rst['list'] as $val){
				$com_uid[] = $val['uid'];
				$res_id[] = $val['resumeid'];
			}
			$company=M('CompanyProfile')->where(array('uid'=>array('in',$com_uid)))->getField('uid,id as company_id,companyname,nature_cn,district_cn,scale_cn,logo,trade_cn,setmeal_id,audit');
			$resume=M('Resume')->where(array('id'=>array('in',$res_id)))->getField('id as resume_id,title,fullname,birthdate,sex,sex_cn,education_cn,experience_cn,intention_jobs,display_name');
			if(C('visitor.utype')==2){
				$down = M('CompanyDownResume')->where(array('resume_uid'=>C('visitor.uid'),'company_uid'=>array('in',$com_uid)))->getfield('company_uid,did');
			}else{
				$favorites = M('CompanyFavorites')->where(array('resume_id'=>array('in',$res_id),'company_uid'=>C('visitor.uid')))->getfield('resume_id,did');
			}
			foreach($rst['list'] as $key=>$val){
				if($val['resume_uid']){
					$company[$val['uid']] && $val = array_merge($val,$company[$val['uid']]);
					$resume[$val['resumeid']] && $val = array_merge($val,$resume[$val['resumeid']]);
					if(C('visitor.utype')==2){
						$val['company_url'] = url_rewrite("QS_companyshow",array('id'=>$val['company_id']));
						$val['hasdown'] = $down[$val['uid']] ? 1 : 0;
					}else{
						$val['hasfavorites'] = $favorites[$val['resumeid']] ? 1: 0;
					}
					if ($val['display_name']=="2"){
						$val['fullname']="N".str_pad($val['id'],7,"0",STR_PAD_LEFT);
					}elseif ($val['display_name']=="3"){
						if($val['sex']==1){
							$val['fullname']=cut_str($val['fullname'],1,0,"先生");
						}elseif($val['sex']==2){
							$val['fullname']=cut_str($val['fullname'],1,0,"女士");
						}
					}
					$y=date("Y");
					if(intval($val['birthdate']) == 0){
						$val['age']='';
					}else{
						$val['age']=$y-$val['birthdate'];
					}
					$val['resume_url'] = url_rewrite("QS_resumeshow",array('id'=>$val['resumeid']));
				}
				$rst['list'][$key] = $val;
			}
		}
		$rst['count'] = $count;
		$rst['page'] = $pager->fshow();
		return $rst;
	}
	/*
		(触屏版)  浏览过的简历 或者 谁看过我
		@$data array 查询条件 为 view_resume 表中条件
		$data['resumeid']=>resumeid;
		$data['addtime']=array('gt',$time);
		$data['r.education']=array('gt',$education);
		$data['r.experience']=array('gt',$experience);
		$data['i.d0']=array('eq',$category); 
		$data['i.d1']=array('eq',$subclass); 
		@$pagesiz 每页显示条数 
		返回值 数组
		$rst['list'] 列表数据 array
		$rst['page'] 分页
	*/
	public function m_aa($data,$pagesize=10){
		$db_pre = C('DB_PREFIX');
		$this_t = $db_pre.'view_resume';
		foreach ($data as $key => $val) {
			$key != 'hasdown' && $where[$this_t.'.'.$key]=$val;
		}
		//if($data['hasdown']) $where['i.company_uid']=array('is',null);//$join3.=' and i.resume_id<>null';
		$join = $db_pre .'resume r on r.id='.$this_t.'.resumeid';
		$join3 = $db_pre .'company_down_resume i on i.resume_id='.$this_t.'.resumeid';
		$join3.=' and i.company_uid='.$this_t.'.uid';
		$count = $this->where($where)->join($join)->join($join3)->count();
		$pager =  pager($count, $pagesize);
		$rst['list'] = $this->where($where)->join($join)->join($join3)->field()->limit($pager->firstRow . ',' . $pager->listRows)->select();
	}
	public function m_get_view_resume($data,$pagesize=10)
	{
		$db_pre = C('DB_PREFIX');
		$this_t = $db_pre.'view_resume';
		// 处理字段重复标识属于哪个表
		foreach ($data as $key => $val) {
			$key != 'hasdown' && $where[$this_t.'.'.$key]=$val;
		}
		$join = 'left join '.$db_pre .'company_down_resume i on i.resume_id='.$this_t.'.resumeid and i.company_uid='.$this_t.'.uid';
		if($data['hasdown'] == '0') $where['i.did']=array('is',null);
		if($data['hasdown']) $where['i.did']=array('is not',null);
		$count = $this->where($where)->join($join)->count();
		$pager =  pager($count, $pagesize);
		$rst['list'] = $this->where($where)->join($join)->field($this_t.'.*,i.did as is_hasdown,i.down_addtime')->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
		if($rst['list']){
			foreach($rst['list'] as $k=>$v){
				$com_uid[]=$v['uid'];
				$res_uid[]=$v['resume_uid'];
			}
			$where1['uid']=array('in',$com_uid);
			$company=M('CompanyProfile')->where($where1)->getField('uid,id,companyname,nature_cn,district_cn,scale_cn,logo,trade_cn,setmeal_id,audit');
			$where2['uid']=array('in',$res_uid);
			$resume=M('Resume')->where($where2)->getField('id,uid,title,fullname,birthdate,sex,sex_cn,education_cn,intention_jobs,display_name');
		}
		foreach ($rst['list'] as $key => $val)
		{
			if($val['resume_uid'])
			{
				$val['companyname']=$company[$val['uid']]['companyname'];
				$val['company_id']=$company[$val['uid']]['id'];
				$val['nature_cn']=$company[$val['uid']]['nature_cn'];
				$val['district_cn']=$company[$val['uid']]['district_cn'];
				$val['scale_cn']=$company[$val['uid']]['scale_cn'];
				$val['setmeal_id']=$company[$val['uid']]['setmeal_id'];
				$val['audit']=$company[$val['uid']]['audit'];
				$val['title']=$resume[$val['resumeid']]['title'];
				$val['fullname']=$resume[$val['resumeid']]['fullname'];
				$val['display_name']=$resume[$val['resumeid']]['display_name'];
				$val['sex']=$resume[$val['resumeid']]['sex'];
				$val['birthdate']=$resume[$val['resumeid']]['birthdate'];
				$val['education_cn']=$resume[$val['resumeid']]['education_cn'];
				$val['intention_jobs']=$resume[$val['resumeid']]['intention_jobs'];
				$val['logo']=$company[$val['uid']]['logo']? attach($company[$val['uid']]['logo'],'company_logo'): attach('no_logo.png','resource');
				$val['trade_cn']=$company[$val['uid']]['trade_cn'];
				// 个人会员中心 谁看过我
				if(C('visitor.utype')==2)
				{
					if($val['is_hasdown']){
						$val['down_addtime'] = $val['down_addtime'];
						$val['hasdown'] = 1;
					}else{
						$val['down_addtime'] = 0;
						$val['hasdown'] = 0;
					}
					
				}
				// 企业会员中心 浏览过的简历
				else
				{
					// 对姓名进行处理
					if($val['display_name']=="3")
					{
						if($val['sex']==1)
						{
							$val['fullname']=cut_str($val['fullname'],1,0,"先生");
						}
						elseif($val['sex']==2)
						{
							$val['fullname']=cut_str($val['fullname'],1,0,"女士");
						}
					}
					elseif($val['display_name']=="2")
					{
						$val['fullname']="N".str_pad($val['resumeid'],7,"0",STR_PAD_LEFT);
					}
					// 年龄
					if(intval($val['birthdate']) == 0)
					{
						$val['age']='';
					}
					else
					{
						$y=date("Y");
						$val['age']=$y-$val['birthdate'];
					}
				}
			}
			$rst['list'][$key] = $val;
		}
		$rst['page'] = $pager->fshow();
		$rst['page_params'] = $pager->get_page_params();
		return $rst;
	}
	/*
		删除 关注我的（企业删除）
		@$yid id 删除id
		
		返回值
		state 删除状态 0失败，1成功
		error 错误信息
		num   删除条数
	*/
	public function del_view_resume($yid)
	{
		if (!is_array($yid)) $yid=array($yid);
		$sqlin=implode(",",$yid);
		if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return array('state'=>0,'error'=>'删除id错误！');
		$where['id']=array('in',$sqlin);
		$where['uid']=C('visitor.uid');
		$num = $this->where($where)->delete();
		if (false===$num) return array('state'=>0,'error'=>'删除失败！');
		//写入会员日志
        foreach (explode(",", $sqlin) as $k => $v) {
            write_members_log(C('visitor'), '', '删除被关注记录（记录id：' . $v . '）');
        }
	  	return array('state'=>1,'num'=>$num);
	}

	/*
		删除 关注我的（个人删除）
		@$yid id 删除id
		
		返回值
		state 删除状态 0失败，1成功
		error 错误信息
		num   删除条数
	*/
	public function personal_del_view_resume($yid)
	{
		if (!is_array($yid)) $yid=array($yid);
		$sqlin=implode(",",$yid);
		if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return array('state'=>0,'error'=>'删除id错误！');
		$where['id']=array('in',$sqlin);
		$where['resume_uid']=C('visitor.uid');
		$num = $this->where($where)->delete();
		if (false===$num) return array('state'=>0,'error'=>'删除失败！');
		//写入会员日志
        foreach (explode(",", $sqlin) as $k => $v) {
            write_members_log(C('visitor'), '', '删除被关注记录（记录id：' . $v . '）');
        }
	  	return array('state'=>1,'num'=>$num);
	}
}

?>