<?php 
namespace Common\Model;
use Think\Model;
class ImMessageModel extends Model{
	protected $_validate = array(
		array('touid,formuid,message','identicalNull','',0,'callback'),
		array('touid,formuid','identicalEnum','',0,'callback'),
		array('message','1,2000','{%im_message_message_length}',0,'length'),
	);
	protected $_auto = array (
		array('messageType','TextMessage'),
		array('mutually','3'),
		array('addtime','time',1,'function')
	);
	public function add_message(){
		if($time = I('post.time',0,'intval')){
			$_POST['addtime'] = substr($time,0,10);
		}
		$formuid = I('post.formuid',0,'intval');
		$touid = I('post.touid',0,'intval');
		$reg = D('ImUser')->add_dialogue($formuid,$touid);
		if(!$reg['state']) return array('state'=>0,'error'=>$reg['error']);
		if(false === $message = $this->create($_POST)) return array('state'=>0,'error'=>$this->getError());
		$message['ftid'] = intval($message['formuid']) + intval($message['touid']);
		if(!$id = $this->add($message)) return array('state'=>0,'error'=>'用户会话信息新增失败！');
		M('ImUser')->where(array('formuid'=>$message['formuid'],'touid'=>$message['touid']))->save(array('message'=>$message['message'],'sendtime'=>$message['addtime']));
		M('ImUser')->where(array('touid'=>$message['formuid'],'formuid'=>$message['touid']))->save(array('message'=>$message['message'],'sendtime'=>$message['addtime'],'unread'=>array('exp','unread+1')));
		$message['addtime'] = date('H:i',$message['addtime']);
		if(C('apply.Weixin')){
			$im = new \Common\qscmslib\im();
			$current_time=time();
			$send_time=$reg['data'][$formuid]['sendtime'];
			$time_diff=$current_time-$send_time;
			if($time_diff>120 && !$im->checkOnline($touid)){
				if(C('visitor.utype')==1){
					$username=D('CompanyProfile')->where(array('uid'=>$formuid))->getField('companyname');
				}elseif(C('visitor.utype')==2){
					$username=D('Resume')->where(array('uid'=>$formuid))->getField('fullname');
				}
				if(!$username){
					$username = D('Members')->where(array('uid'=>$formuid))->getField('username');
				}
				D('Weixin/TplMsg')->set_rongyun_pms($touid,$formuid,$reg['data'][$formuid]['sendtime'],$username,$message['message']);
			}
		}
		return array('state'=>1,'message'=>$message);
	}
	/**
	 * [get_message 读取历史消息]
	 */
	public function get_message($uid,$pagesize=20){
		$dialog = M('ImUser')->field('id,addtime')->where(array('formuid'=>C('visitor.uid'),'touid'=>$uid))->find();
		if(!$dialog) return array('stats'=>1,'data'=>array('newTime'=>0,'list'=>'','is_load'=>0));
		$limit = 20;
		$ftid = intval(C('visitor.uid')) + intval($uid);
		$where = "ftid={$ftid} and (formuid={$uid} or touid={$uid}) and addtime>={$dialog['addtime']}";
		$count = $this->where($where)->count('id');
		if(!$count) return array('stats'=>1,'data'=>array('newTime'=>0,'list'=>'','is_load'=>0));
		$pager =  pager($count,$pagesize);
		$message = $this->where($where)->order('addtime desc,id asc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
		$p = I('get.page',1,'intval');
		$is_load = $count <= $p * $pagesize ? 0 : 1;
		return array('state'=>1,'data'=>array('newTime'=>$message[0]['addtime'],'list'=>array_reverse($message),'is_load'=>$is_load));
	}
}
?>