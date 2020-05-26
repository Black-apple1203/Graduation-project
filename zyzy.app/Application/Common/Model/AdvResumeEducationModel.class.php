<?php
namespace Common\Model;
use Think\Model;
class AdvResumeEducationModel extends Model{
	protected $_validate = array(
		array('pid,startyear,startmonth,education','identicalNull','',0,'callback'),
		array('endyear,endmonth,todate','timeRequired','{%resume_education_end_time_required}',1,'callback'),
		array('pid,uid,startyear,startmonth,endyear,endmonth,education,todate,campus_id','identicalEnum','',0,'callback'),
		array('school','1,100','{%resume_education_school_length_error}',1,'length'),//学校名称
		array('speciality','1,100','{%resume_education_speciality_length_error}',1,'length'),//专业
	);
	/**
	 * [教育结束时间验证]
	 * 教育结束年份+月份、至今必选一项
	 * @param  [array] $data
	 * @return [boolean]
	 */
	protected function timeRequired($data){
		if($data['todate']) return true;
		if($data['endyear'] && $data['endmonth']) return true;
		return false;
	}
	/*
		添加教育经历
	*/
	public function add_resume_education($data,$user)
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
		write_members_log($user,'resume','添加简历教育经历（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$insert_id);
	}
	/*
		保存教育经历
	*/
	public function save_resume_education($data,$user)
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
		write_members_log($user,'resume','修改高级简历教育经历（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$data['id'],'data'=>$data);
	}
	/*
		获取简历的教育经历
	*/
	public function get_resume_education($id,$uid=false)
	{
		$where['pid'] = array('eq',$id);
		if($uid){
			$where['uid'] = array('eq',$uid);
		}
		$edu_list = $this->where($where)->select();
		return $edu_list;
	}
	/*
		删除简历的教育经历
	*/
	public function del_resume_education($del_id,$pid,$user)
	{
		if (!is_array($del_id)) $del_id=array($del_id);
		$where['id']=array('in',$del_id);
		$num = $this->where($where)->delete();
		if(false === $num) return array('state'=>0,'error'=>'删除失败！');
		return array('state'=>1,'num'=>$num);
	}
}
?>