<?php 
namespace Common\Model;
use Think\Model\RelationModel;
class AdminModel extends RelationModel{
		protected $_validate = array(
			array('username,email,role_id','identicalNull','',0,'callback'),
			array('username','1,60','{%admin_username_length_error}',0,'length'),
			array('password','4,16','{%admin_length_error_password}',0,'length',3),
			array('role_id','number','{%admin_format_error_role_id}',0),
			array('email','email','{%admin_format_error_email}'),
			array('password','repassword','{%admin_equal_error_rpwd}',0,'confirm',3),
			array('username','','{%admin_unique_error_username}',0,'unique')
		);
		protected $_auto = array (
			array('pwd_hash','randstr',3,'callback'),
			array('add_time','time',1,'function')
		);
		protected $_link = array(
	        //关联角色
	        'role' => array(
	            'mapping_type' => self::BELONGS_TO,
	            'class_name' => 'AdminRole',
	            'foreign_key' => 'role_id',
	        )
	    );
		/*
			获取随机字符串
		*/
	    protected function randstr($length=6){
	        $hash='';
	        $chars= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz@#!~?:-='; 
	        $max=strlen($chars)-1;   
	        mt_srand((double)microtime()*1000000);   
	        for($i=0;$i<$length;$i++)   {   
	        $hash.=$chars[mt_rand(0,$max)];   
	        }
	        return $hash;
	    }
	}
?>