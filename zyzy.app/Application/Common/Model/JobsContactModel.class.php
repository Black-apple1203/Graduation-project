<?php
namespace Common\Model;
use Think\Model;
class JobsContactModel extends Model{
	protected $_validate = array(
		array('pid','require','{%jobs_contact_null_error_pid}',0,'regex'),
		array('telephone','mobile','{%telephone_format_error}',2),
		// array('landline_tel','tel','{%landline_tel_format_error}',2,'regex'), // 固定电话
		array('email','email','{%email_format_error}',2),
		array('pid','number','{%jobs_contact_enum_error_pid}'),
	);
	protected $_auto = array ( 
		array('contact_show',1),//是否显示联系人
		array('telephone_show',1),//是否显示联系电话
		array('email_show',1),//是否显示联系邮箱
		array('qq_show',1),//是否显示联系qq
		array('landline_tel_show',1),
	);
	/* 
		添加职位联系方式
	*/
	public function add_jobs_contact($data)
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
	/* 
		修改职位联系方式
	*/
	public function edit_jobs_contact($data)
	{
		if(false === $this->create($data))
		{
			return array('state'=>0,'error'=>$this->getError());
		}
		else
		{
			if(false === $this->where(array('pid'=>$data['pid']))->save())
			{
				return array('state'=>0,'error'=>'数据修改失败！');
			}
		}
		return array('state'=>1,'id'=>$data['pid']);
	}
}
?>