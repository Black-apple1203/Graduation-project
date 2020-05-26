<?php 
namespace Common\Model;
use Think\Model;
class ConsultantModel extends Model{
	protected $_validate = array(
		array('name,qq','identicalNull','',1,'callback'),
	);
	/*
		为企业随机分配顾问
		@uid 企业 uid 
	*/
	public function set_consultant($user){
		$consultants = $this->where($where)->getField('id',true);
		if($consultants){
			$rand = array_rand($consultants,1);
			$consultant = $consultants[$rand];
		}else{
			$consultant = 0;
		}
		return D('Members')->where('uid='.$user['uid'])->setField('consultant',$consultant);
	}
}
?>