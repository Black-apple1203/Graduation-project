<?php
namespace Common\Model;
use Think\Model;
class PersonalFavoritesModel extends Model{
	protected $_validate = array(
		array('personal_uid,jobs_id,jobs_name','identicalNull','',0,'callback'),
		array('personal_uid,jobs_id','identicalEnum','',0,'callback'),
	);
	protected $_auto = array ( 
		array('addtime','time',1,'function'),
	);
	/*
		获取 收藏职位列表
		$data  查询条件 为 personal_favorites 表中条件 
		$data['personal_uid']=session('uid');
		$data['addtime']=array('gt',$addtime);

		返回值 数组
		$rst['list'] 数据列表 array
		$rst['page'] 分页
	*/
	public function get_favorites($data,$pagesize=10,$jobsdata)
	{
		$db_pre = C('DB_PREFIX');
		$this_t = $db_pre.'personal_favorites';
		foreach ($data as $key => $value){
			$where[$this_t.'.'.$key]=$value;
		}
		foreach ($jobsdata as $keys => $values){
			$where[$keys]=$values;
		}
		$join = 'left join '.$db_pre .'jobs j on j.id='.$this_t.'.jobs_id';
		$count = $this->where($where)->join($join)->count();
		$pager =  pager($count, $pagesize);
		$rst['list'] = $this->where($where)->join($join)->field($this_t.'.*,j.uid as company_uid,j.district,j.age,j.addtime as jobs_addtime,j.companyname,j.company_addtime,j.company_id,j.minwage,j.maxwage,j.negotiable,j.district_cn,j.deadline,j.refreshtime,j.click,j.category_cn,j.tag_cn,j.education_cn,j.experience_cn,j.display')->order('did desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
		foreach ($rst['list'] as $key => $val)
		{
			if (empty($val['companyname'])){
				$jobs = M('JobsTmp')->where(array('id'=>$val['jobs_id']))->find();
				if($jobs){
					$val['company_uid']=$jobs['uid'];
					$val['jobs_name']=$jobs['jobs_name'];
					$val['jobs_addtime']=$jobs['addtime'];
					$val['companyname']=$jobs['companyname'];
					$val['company_addtime']=$jobs['company_addtime'];
					$val['company_id']=$jobs['company_id'];
					$val['minwage']=$jobs['minwage'];
					$val['maxwage']=$jobs['maxwage'];
					$val['district']=$jobs['district'];
					$val['district_cn']=$jobs['district_cn'];
					$val['deadline']=$jobs['deadline'];
					$val['refreshtime']=$jobs['refreshtime'];
					$val['click']=$jobs['click'];
					$val['negotiable']=$jobs['negotiable'];
					$val['display']=$jobs['display'];
					$val['tmp']=1;
				}
			}else{
				$val['tmp']=0;
			}
			$age = explode('-',$val['age']);
            if(!$age[0] && !$age[1]){
                $val['age_cn'] = '不限';
            }else{
                $age[0] && $val['age_cn'] = $age[0].'岁以上';
                $age[1] && $val['age_cn'] = $age[1].'岁以下';
            }
			if($val['negotiable']==0){
                $val['minwage'] = $val['minwage']%1000==0?(($val['minwage']/1000).'K'):(round($val['minwage']/1000,1).'K');
                $val['maxwage'] = $val['maxwage']?($val['maxwage']%1000==0?(($val['maxwage']/1000).'K'):(round($val['maxwage']/1000,1).'K')):'';
                $val['maxwage'] = $val['maxwage']?('-'.$val['maxwage']):'';
                $val['wage_cn'] = $val['minwage'].$val['maxwage'].'/月';
            }else{
                $val['wage_cn'] = '面议';
            }
            if($val['tag_cn']){
            	$val['tag_arr'] = explode(',',$val['tag_cn']);
            }
			$val['company_url'] = url_rewrite('QS_companyshow',array('id'=>$val['company_id']));
			$val['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$val['jobs_id']));
			$rst['list'][$key] = $val;
		}
		$rst['page'] = $pager->fshow();
		$rst['page_params'] = $pager->get_page_params();
		return $rst;
	}
	/*
		删除 收藏职位
		@$id 删除id 多个用,分割
		@$user 会员信息 uid,username,utype 
		
		返回值 数组
		@state 删除状态 0 失败，1成功
		@error 错误信息
		@num   删除行数
	*/
	public function del_favorites($id,$user)
	{
		$where['personal_uid']=$user['uid'];
		if (!is_array($id)) $id=array($id);
		$sqlin=implode(",",$id);
		if (!fieldRegex($sqlin,'in')) return array('state'=>false,'error'=>'请选择职位！');
		$where['did']=array('in',$sqlin);
		$num = $this->where($where)->delete();
		if(false === $num) return array('state'=>false,'error'=>'删除失败！');
		//写入会员日志
		write_members_log($user,'','删除收藏的职位（记录id：'.$sqlin.'）');
		return array('state'=>true,'num'=>$num);
	}
	/*
		添加收藏职位
		@$id 收藏职位id
		@$uid 会员uid

		返回值 数组
		@state 删除状态 0 失败，1成功
		@error 错误信息
		@num   收藏数
	*/
	public function add_favorites($id,$user)
	{
		if(!fieldRegex($id,'in')) return array('state'=>0,'error'=>'请选择正确的职位！');
		$jobs = M('Jobs')->where(array('id'=>array('in',$id)))->select();
		$fids = $this->where(array('jobs_id'=>array('in',$id),'personal_uid'=>$user['uid']))->getField('jobs_id,did');
		foreach($jobs as $val){
			if(isset($fids[$val['id']])){
				return array('state'=>0,'error'=>'您已经收藏该职位,不能重复收藏！');
			}
			$setsqlarr[]=array('personal_uid'=>$user['uid'],'jobs_id'=>$val['id'],'jobs_name'=>$val['jobs_name'],'addtime'=>time());
		}
		if(!$this->addAll($setsqlarr)) return array('state'=>0,'error'=>'收藏失败!');
		return array('state'=>1,'num'=>count($setsqlarr));
	}
}
?>