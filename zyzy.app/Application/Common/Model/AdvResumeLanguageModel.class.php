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
// | ModelName: 简历语言能力表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class AdvResumeLanguageModel extends Model{
	protected $_validate = array(
		array('pid,language,level','identicalNull','',1,'callback'),
		array('pid,language,level','identicalEnum','',1,'callback'),
	);
	/*
		添加语言
	*/
	public function add_resume_language($data,$user)
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
		write_members_log($user,'resume','添加高级简历语言能力（简历id：'.$data['pid'].'）',false,array('resume_id'=>$data['pid']));
		return array('state'=>1,'id'=>$insert_id);
	}
	/*
		保存语言
	*/
	public function save_resume_language($data,$user)
	{
		if(false === $this->create($data))
		{
			return array('state'=>0,'error'=>$this->getError());
		}
		else
		{
			if(false === $this->save())
			{
				return array('state'=>0,'error'=>'数据修改失败！');
			}
		}
		return array('state'=>1,'id'=>$data['id']);
	}

	public function get_resume_language($id,$uid=false)
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