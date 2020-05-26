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
// | ModelName: 简历工作经验表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class AdvResumeWorkModel extends Model{
	protected $_validate = array(
		//用户UID、简历ID、开始年、开始月不为空验证
		array('pid,startyear,startmonth','identicalNull','',1,'callback'),
		//结束时间与至今必选一验证
		array('endyear,endmonth,todate','timeRequired','{%resume_work_end_time_required}',1,'callback'),
		//公司名称长度验证
		array('companyname','1,100','{%resume_work_companyname_length_error}',1,'length'),
		//职务名称长度验证
		array('jobs','1,60','{%resume_work_jobs_length_error}',1,'length'),
		//业绩或表现长度验证
		array('achievements','0,1000','{%resume_work_achievements_length_error}',0,'length'),
		//用户UID、简历ID、开始年、开始月、结束年、结束月、至今数字验证
		array('pid,startyear,startmonth,endyear,endmonth,todate','identicalEnum','',1,'callback'),
	);
	/**
	 * [工作经验结束时间验证]
	 * 工作经验结束年份+月份、至今必选一项
	 * @param  [array] $data
	 * @return [boolean]
	 */
	protected function timeRequired($data){
		if($data['todate']) return true;
		if($data['endyear'] && $data['endmonth']) return true;
		return false;
	}
	/*
		添加工作经历
	*/
	public function add_resume_work($data,$user)
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
		write_members_log($user,'resume','添加简历工作经历（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$insert_id);
	}
	/*
		保存工作经历
	*/
	public function save_resume_work($data,$user)
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
		//写入会员日志
		write_members_log($user,'resume','修改高级简历工作经历（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$data['id'],'data'=>$data);
	}
	/*
		获取简历的工作经历
	*/
	public function get_resume_work($id,$uid=false)
	{
		$where['pid'] = array('eq',$id);
		if($uid){
			$where['uid'] = array('eq',$uid);
		}
		$work_list = $this->where($where)->select();
		return $work_list;
	}
	/*
		删除简历的工作经历
	*/
	public function del_resume_work($del_id,$pid,$user)
	{
		if (!is_array($del_id)) $del_id=array($del_id);
		$where['id']=array('in',$del_id);
		$num = $this->where($where)->delete();
		if(false === $num) return array('state'=>0,'error'=>'删除失败！');
		return array('state'=>1,'num'=>$num);
	}
}
?>