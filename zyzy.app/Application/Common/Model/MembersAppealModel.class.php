<?php 
namespace Common\Model;
use Think\Model;
class MembersAppealModel extends Model{
	protected $_validate = array(
		array('realname,mobile,email,description','identicalNull','',0,'callback'),
		array('mobile','mobile','{%members_appeal_format_error_mobile}',2),
		array('email','email','{%members_appeal_format_error_email}',2),
	);
	protected $_auto = array (
		array('addtime','time',1,'function'),
		array('status',0),
	);
}
?>