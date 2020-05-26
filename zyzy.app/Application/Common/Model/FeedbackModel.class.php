<?php 
namespace Common\Model;
use Think\Model;
class FeedbackModel extends Model
{
	protected $_validate = array(
		array('infotype,feedback,tel','identicalNull','',1,'callback'),
		array('tel','_tel','{%feedback_tel}',0,'callback'),
	);
	protected $_auto = array (
		array('addtime','time',1,'function'),
		array('audit',1),
	);
	protected function _tel($data){
		if(!fieldRegex($data,'tel') && !fieldRegex($data,'mobile') && !fieldRegex($data,'email') && !fieldRegex($data,'qq')) return false;
		return true;
	}
	//添加用户反馈
	public function addeedback($data){

		if(false === $this->create($data)) return array('state'=>0,'msg'=>$this->getError());
		if(false === $insertid = $this->add()) return array('state'=>0,'msg'=>'数据保存失败！');

		if(false !== $insertid) return array('state'=>1,'msg'=>'反馈成功，感谢您对本站的关注！','insertid'=>$insertid);//修改信息
	}
}	
?>