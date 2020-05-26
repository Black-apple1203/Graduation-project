<?php
// +----------------------------------------------------------------------
// | 74CMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.74cms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | ModelName: 简历项目经历表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class ResumeProjectModel extends Model{
	protected $_validate = array(
		//用户UID、简历ID、开始年、开始月不为空验证
		array('uid,pid,startyear,startmonth','identicalNull','',1,'callback'),
		//结束时间与至今必选一验证
		array('endyear,endmonth,todate','timeRequired','{%resume_project_end_time_required}',1,'callback'),
		//项目名称长度验证
		array('projectname','1,100','{%resume_project_projectname_length_error}',1,'length'),
		//担任角色长度验证
		array('role','1,100','{%resume_project_role_length_error}',1,'length'),
		//项目描述长度验证
		array('description','1,1000','{%resume_project_description_length_error}',1,'length'),
		//字段数字验证
		array('uid,pid,startyear,startmonth,endyear,endmonth,todate','identicalEnum','{%resume_project_end_time_enum}',1,'callback'),
	);
	/**
	 * [项目结束时间验证]
	 * 项目结束年份+月份、至今必选一项
	 * @param  [array] $data
	 * @return [boolean]
	 */
	protected function timeRequired($data){
		if($data['todate']) return true;
		if($data['endyear'] && $data['endmonth']) return true;
		return false;
	}
	/*
		添加项目经历
	*/
	public function add_resume_project($data,$user)
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
		//写入会员日志
		write_members_log($user,'resume','添加简历项目经历（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		$resume_mod = D('Resume');
		$resume_mod->check_resume($user['uid'],$data['pid']);
		return array('state'=>1,'id'=>$insert_id);
	}
	/*
		保存培项目经历
	*/
	public function save_resume_project($data,$user)
	{
		if(false === $this->create($data))
		{
			return array('state'=>0,'error'=>$this->getError());
		}
		else
		{
			if(false === $this->save())
			{
				return array('state'=>0,'error'=>'数据添加失败！');
			}
		}
		D('Resume')->check_resume($user['uid'],$data['pid']);
		//写入会员日志
		write_members_log($user,'resume','修改简历项目经历（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$data['id']);
	}
	/*
		获取简历的项目经历
	*/
	public function get_resume_project($id,$uid=false)
	{
		$where['pid'] = array('eq',$id);
		if($uid){
			$where['uid'] = array('eq',$uid);
		}
		$train_list = $this->where($where)->select();
		return $train_list;
	}
	/*
		删除简历的项目经历
	*/
	public function del_resume_project($del_id,$pid,$user)
	{
		if (!is_array($del_id)) $del_id=array($del_id);
		$where['id']=array('in',$del_id);
		$num = $this->where($where)->delete();
		if(false === $num) return array('state'=>0,'error'=>'删除失败！');
		return array('state'=>1,'num'=>$num);
	}
}
?>