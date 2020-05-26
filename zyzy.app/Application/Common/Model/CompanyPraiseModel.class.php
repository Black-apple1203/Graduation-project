<?php 
namespace Common\Model;
use Think\Model;
class CompanyPraiseModel extends Model
{
	protected $_validate = array(
		array('company_id,uid,click_type','identicalNull','',1,'callback'),
		array('company_id,uid,click_type','identicalEnum','',0,'callback'),

	);
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),
		array('addtime','time',1,'function'),
	);
}
?>