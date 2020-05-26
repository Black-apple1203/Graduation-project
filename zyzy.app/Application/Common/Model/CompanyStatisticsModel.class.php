<?php 
namespace Common\Model;
use Think\Model;
class CompanyStatisticsModel extends Model
{
	protected $_auto = array (
		array('comid',0),
		array('uid',0),
		array('source',1),
		array('jobid',0),
		array('apply',0),
		array('addtime','getTime',1,'callback'),
		array('viewtime','time',1,'function'),
	);

	protected function getTime(){
		return strtotime(date('Y-m-d'));
	}
}
?>