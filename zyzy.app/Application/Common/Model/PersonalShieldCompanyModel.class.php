<?php
namespace Common\Model;
use Think\Model;
class PersonalShieldCompanyModel extends Model{
	protected $_validate = array(
		array('uid','require','personal_shield_company_null_error_uid',1),
		array('uid','number','{%personal_shield_company_enum_error_uid}',1),
		array('comkeyword','1,30','{%personal_shield_company_length_error_comkeyword}',0,'length'),
	);
}
?>