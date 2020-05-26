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
// | ModelName: 简历简历外发记录表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class ResumeOutwardModel extends Model{
	protected $_validate = array(
		array('uid,resume_id,email','identicalNull','',1,'callback'),
		array('uid,resume_id','identicalEnum','',1,'callback'),
		array('jobs_name','1,40','{%resume_outward_jobs_name_length_error}',1,'length'),
		array('companyname','1,40','{%resume_outward_companyname_length_error}',1,'length'),
		array('email','emailBreak','{%resume_outward_email_validate_error}',1,'callback'),
	);
	protected $_auto = array (
		array('addtime','time',1,'function'), //添加时间 
	);
	/**
	 * [邮箱批量验证]
	 * @param  [string] $data ['123@qq.com;78648@qq.com']
	 * @return [boolean]
	 */
	protected function emailBreak($data){
		$email = explode(';',$data);
		foreach ($email as $val) {
			if(false === fieldRegex($val,'email')) return false;
		}
		return true;
	}
}
?>