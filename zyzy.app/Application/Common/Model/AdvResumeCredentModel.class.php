<?php
namespace Common\Model;
use Think\Model;
class AdvResumeCredentModel extends Model{
	protected $_validate = array(
		array('pid,year,month','identicalNull','',1,'callback'),
		array('pid,year,month','identicalEnum','',1,'callback'),
		array('name','1,30','{%resume_credent_name_length_error}',1,'length'), // 证书
	);
	public function add_resume_credent($data,$user)
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
		write_members_log($user,'resume','添加简历证书（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$insert_id);
	}
	public function save_resume_credent($data,$user)
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
		write_members_log($user,'resume','修改简历证书（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$data['id']);
	}
	public function get_resume_credent($id,$uid=false)
	{
		$where['pid'] = array('eq',$id);
		if($uid){
			$where['uid'] = array('eq',$uid);
		}
		$list = $this->where($where)->select();
		return $list;
	}
}
?>