<?php
namespace Common\Model;
use Think\Model;
class PersonalServiceStickModel extends Model{
	protected $_validate = array(
		array('days,points','identicalNull','',0,'callback'),
		array('days,points','identicalEnum','',0,'callback'),
	);
}
?>