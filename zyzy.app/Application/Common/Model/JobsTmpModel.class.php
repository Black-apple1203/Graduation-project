<?php
namespace Common\Model;
use Think\Model;
class JobsTmpModel extends Model{
	protected $_validate = array(
		array('uid,jobs_name,companyname,company_id,company_addtime,nature,amount,topclass,category,subclass,trade,scale,district,tag,education,experience,wage,contents,deadline,setmeal_deadline,setmeal_id,key,tpl','identicalNull','',0,'callback'),
		array('uid,company_id,company_addtime,stick,nature,amount,topclass,category,subclass,trade,scale,tag,education,experience,wage,deadline,setmeal_deadline,setmeal_id','identicalEnum','',0,'callback'),
		array('jobs_name,companyname,tpl','identicalLength_50','',0,'callback'),
	);
	protected $_auto = array ( 
		array('company_audit',0),//审核
		array('emergency',0),//紧急招聘
		array('negotiable',0),//面议
		array('addtime','time',1,'function'),//添加时间
		array('refreshtime','time',1,'function'),//刷新时间
		array('audit',1),//是否审核通过
		array('display',1),//是否显示
		array('click',1),//点击量
		array('user_status',1),//用户身份
		array('robot',0),//是否为采集信息
		array('add_mode',1),//添加方式
        array('sex', 0),//性别
	);
	/**
	 * 验证企业职位表字段合法性
	 * 验证指定字段长度
	 * @param array $data 被验证字段
	 * 
	 * @return Boolean/string 验证结果(true:合法,false:不合法,字符串：不合法提示语)
	 */
	protected function identicalLength_50($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=50) return 'jobs_tmp_length_error_'.$key;
		}
		return true;
	}
	/*
		修改职位
		@$data POST 值
	*/
	public function edit_jobs($data)
	{
		// 职位表
		if(false === $this->create($data))
		{
			return array('state'=>0,'error'=>$this->getError());
		}
		else
		{
			if(false === $this->save())
			{
				return array('state'=>0,'error'=>'更新失败！');
			}
		}
		
		return array('state'=>1,'id'=>$data['id']);
	}
}
?>