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
// | ModelName: 短信营销表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class SmsqueueModel extends Model{
	// protected $_validate = array(
	// 	//用户UID、发送短信内容不为空验证
	// 	array('s_body','identicalNull','',1),
	// 	//手机格式验证
	// 	array('s_mobile','mobileBreak','{%smsqueue_s_mobile_validate_error}',1,'callback'),
	// 	//发送短信类型、用户UID数字验证
	// 	array('s_type','identicalEnum','',1),
	// );
	protected $_auto = array (
		array('s_addtime','time',1,'function'), //添加时间
		array('s_sendtime','time',2,'function'), //发送时间
		array('s_type',1), //状态
	);
	/**
	 * [邮箱批量验证]
	 * @param  [string] $data ['15963552415;13652458865']
	 * @return [boolean]
	 */
	protected function mobileBreak($data){
		$email = explode(';',$data);
		foreach ($email as $val) {
			if(false === fieldRegex($val,'mobile')) return false;
		}
		return true;
	}
}
?>