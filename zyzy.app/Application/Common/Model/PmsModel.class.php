<?php
namespace Common\Model;
use Think\Model;
class PmsModel extends Model{
	protected $_validate = array(
		array('msgtouid,message','identicalNull','',0,'callback'),
	);
	protected $_auto = array ( 
		array('dateline','time',1,'function'),
		array('msgtype',1),
		array('new',1),
		array('replyuid',0),
	);
	// 消息
	public function write_pmsnotice($touid,$toname,$message,$utype){
		$setsqlarr['message']=trim($message);
		$setsqlarr['msgtouid']=intval($touid);
		$setsqlarr['msgtoutype']=$utype;
		$setsqlarr['msgtoname']=trim($toname);
		$setsqlarr['replytime']=time();
		if(false === $this->create($setsqlarr))
		{
			return array('state'=>0,'error'=>$this->getError());
		}
		else
		{
			if(false === $insert_id = $this->add())
			{
				return array('state'=>0,'error'=>'数据添加失败！');
			}
		}
		return array('state'=>1,'id'=>$insert_id);
	}
	// 消息
	public function update_pms_read($user,$perpage=10,$where=array()){
		$where['msgtouid'] = $user['uid'];
		$where['msgtoutype'] = array(array('eq',$user['utype']),array('eq',0),'or');
        $rst['count']=$this->where($where)->count();
        $pager =  pager($rst['count'], $perpage);
        $rst['list'] = $this->where($where)->order('pmid desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
		$rst['page'] = $pager->fshow();
		return $rst;
	}
	/**
     * [msg_check 系统消息查看]
     */
	public function msg_check($ids,$user){
        if(!$ids) return array('state'=>0,'error'=>'请选择消息！');
        if(is_array($ids)){
        	$ids = implode(',',$ids);
        	if(!fieldRegex($ids,'in')) return array('state'=>0,'error'=>'请正确选择消息！');
        	$where['pmid'] = array('in',$ids);
        }else{
        	$where['pmid'] = intval($ids);
        }
        $where['msgtouid'] = C('visitor.uid');
        if(!$msg = $this->where($where)->find()) return array('state'=>0,'error'=>'请正确选择消息！');
        $where['new'] = array('neq',2);
        if(false === $count = $this->where($where)->setfield('new',2)) return array('state'=>0,'error'=>'消息已删除或不存在！');
        $count && M('MembersMsgtip')->where(array('uid'=>$user['uid']))->setDec('unread',$count);
        //写入会员日志
		write_members_log($user,'','查看系统消息（id：'.$ids.'）');
        return array('state'=>1,'data'=>$msg);
	}
	/**
	 * [del_msg 系统消息删除]
	 */
	public function msg_del($ids,$user){
		if(!$ids) return array('state'=>0,'error'=>'请选择消息！');
		!is_array($ids) && $ids = array($ids);
		$pids = M('Pms')->where(array('pmid'=>array('in',$ids),'msgtouid'=>C('visitor.uid'),'new'=>array('neq',2)))->count('pmid');
        $result = M('Pms')->where(array('pmid'=>array('in',$ids),'msgtouid'=>C('visitor.uid')))->delete();
        if($result){
        	$pids && M('MembersMsgtip')->where(array('uid'=>$user['uid']))->setDec('unread',$pids);
        	//写入会员日志
			write_members_log($user,'','删除系统消息（id：'.implode(",", $ids).'）');
        	return array('state'=>1,'error'=>'删除成功！');
        }else{
        	return array('state'=>0,'error'=>'删除失败！');
        }
	}
	protected function _after_insert($data,$options){
    	M('MembersMsgtip')->where(array('uid'=>$data['msgtouid']))->setInc('unread');
    }
	/**
	 * [_addAll_after_insert description]
	 */
	protected function _addAll_after_insert($data,$options,$count){
		foreach ($data as $key => $val) {
			$uids[] = $val['msgtouid'];
		}
		M('MembersMsgtip')->where(array('uid'=>array('in',$uids)))->setInc('unread');
	}
}
?>