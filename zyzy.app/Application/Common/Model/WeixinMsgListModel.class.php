<?php
namespace Common\Model;
use Think\Model;
class WeixinMsgListModel extends Model{
	protected $_validate = array(
		array('uid','require','{%weixin_msg_uid_error}',1),
		array('uid,utype','identicalEnum','',2,'callback'),
		array('username','1,30','{%weixin_msg_username_length_error}',0,'length'),
	);
	protected $_auto = array (
		array('sendtime','time',1,'function'),
		array('status',1),
	);
}
?>