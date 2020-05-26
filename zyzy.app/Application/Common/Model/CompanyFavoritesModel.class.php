<?php 
namespace Common\Model;
use Think\Model;
class CompanyFavoritesModel extends Model
	{
		protected $_validate = array(
			array('resume_id,company_uid','identicalNull','',1,'callback'),
			array('resume_id,company_uid','identicalEnum','',1,'callback'),
		);
		protected $_auto = array (
			array('favorites_addtime','time',1,'function'),
		);
		/*
			获取收藏的简历
		*/
		public function get_favorites($data,$pagesize=10)
		{
			$where = $data;
			$db_pre = C('DB_PREFIX');	
			$this_t = $db_pre.'company_favorites';
			$join = 'left join '.$db_pre .'resume r on r.id='.$this_t.'.resume_id';
			$count = $this->where($where)->join($join)->count();
			$pager =  pager($count, $pagesize);
			$rst['list'] = $this->where($where)->join($join)->field($this_t.'.*,r.title,r.display_name,r.sex,r.fullname,r.sex_cn,r.birthdate,r.experience_cn,r.education_cn,r.intention_jobs')->order('did desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
			foreach ($rst['list'] as $key => $val) 
			{
				if ($val['display_name']=="2")
				{
					$val['fullname']="N".str_pad($val['resume_id'],7,"0",STR_PAD_LEFT);
				}
				elseif ($val['display_name']=="3")
				{
					if($val['sex']==1){
						$val['fullname']=cut_str($val['fullname'],1,0,"先生");
					}
					elseif($val['sex']==2){
						$val['fullname']=cut_str($val['fullname'],1,0,"女士");
					}
				}
				$val['jobs_name_']=cut_str($val['jobs_name'],7,0,"...");
				$val['resume_url']=url_rewrite('QS_resumeshow',array('id'=>$val['resume_id'],'apply'=>1));
				$y=date("Y");
				if(intval($val['birthdate']) == 0)
				{
					$val['age']='';
				}
				else
				{
					$val['age']=$y-$val['birthdate'];
				}
				/* 教育经历 培训经历 */
				// $val['resume_education_list']=M('ResumeEducation')->where(array('uid'=>$val['ruid'],'pid'=>$val['resume_id']))->select();
				// $val['resume_work_list']=M('ResumeWork')->where(array('uid'=>$val['ruid'],'pid'=>$val['resume_id']))->select();
				/*
					获取简历标记
				*/
				// $val_state= M('CompanyLabelResume')->where(array('uid'=>$data['company_uid'],'resume_id'=>$val['resume_id']))->find();
				// $val['resume_state']=$val_state['resume_state'];
				// $val['resume_state_cn']=$val_state['resume_state_cn'];
				$rst['list'][$key] = $val;
			}
			$rst['count'] = $count;
			$rst['page'] = $pager->fshow();
			$rst['page_params'] = $pager->get_page_params();
			return $rst;
		}
		/*
			删除已下载简历
		*/
		public function del_favorites($del_id,$user)
		{
			if (!is_array($del_id)) $del_id=array($del_id);
			$where['did']=array('in',$del_id);
			$where['company_uid']=$user['uid'];
			$num = $this->where($where)->delete();
			if(false === $num) return array('state'=>0,'error'=>'删除失败！');
			//写入会员日志
			write_members_log($user,'','删除已下载简历，记录id：'.implode(",", $del_id));
			return array('state'=>1,'num'=>$num);
		}
		
		/**
		 * 保存收藏简历
		 */
		public function add_favorites($id,$company_user){
			$company_uid = $company_user['uid'];
			$timestamp=time();
			$count = $this->where(array('company_uid'=>$company_uid))->count();
			if (strpos($id,",")) $id=explode(",",$id);
			if(is_array($id)){
				return $this->_add_favorites_batch($id,$company_user);
			}else{
				return $this->_add_favorites_one($id,$company_user);
			}
			
		}
		/**
		 * 批量收藏简历
		 */
		protected function _add_favorites_batch($id,$company_user){
			$company_uid = $company_user['uid'];
			$num=0;
			foreach ($id as $v) {
				$v=intval($v);
				if (!$this->where(array('company_uid'=>$company_uid,'resume_id'=>$v))->find()){
					$setarr['resume_id']=$v;
					$setarr['company_uid']=$company_uid;
					$setarr['favorites_addtime']=$timestamp;
					if(false === $this->create($setarr)) return array('state'=>0,'error'=>$this->getError());
					if(false === $insert_id = $this->add()) return array('state'=>0,'error'=>'数据添加失败！');
					$num++;
				}
			}
			if($num == 0){
				return array('state'=>0,'error'=>'收藏简历失败！');
			}
			//写入会员日志
			write_members_log($company_user,'','收藏简历（简历id：'.implode(",", $id).'）');
			return array('state'=>1,'i'=>$num,'error'=>'成功收藏'.$num.'份简历！');
		}
		/**
		 * 单个收藏简历
		 */
		protected function _add_favorites_one($id,$company_user){
			$company_uid = $company_user['uid'];
			if (!$this->where(array('company_uid'=>$company_uid,'resume_id'=>$id))->find()){
				$setarr['resume_id']=$id;
				$setarr['company_uid']=$company_uid;
				$setarr['favorites_addtime']=$timestamp;
				if(false === $this->create($setarr)) return array('state'=>0,'error'=>$this->getError());
				if(false === $insert_id = $this->add()) return array('state'=>0,'error'=>'数据添加失败！');
				//写入会员日志
				write_members_log($company_user,'','收藏简历（简历id：'.$id.'）');
				return array('state'=>1,'i'=>$num,'error'=>'收藏成功！');
			}
			return array('state'=>0,'error'=>'收藏失败！您已收藏过该简历');
		}
	}
?>