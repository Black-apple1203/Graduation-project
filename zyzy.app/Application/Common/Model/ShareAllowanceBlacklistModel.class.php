<?php 
namespace Common\Model;
use Think\Model;
class ShareAllowanceBlacklistModel extends Model{
	protected $_validate = array(
        array('uid','identicalNull','',0,'callback'),
        array('uid','identicalEnum','',0,'callback'),
        array('note','0,200','{%share_allowance_blacklist_note_length}',2,'length')
	);
    protected $_auto = array(
        array('addtime','time',1,'function')
    );
    public function addBlacklist($data){
        if(false === $this->create($data)){
            return array('state'=>0,'msg'=>$this->getError());
        }elseif(!$id = $this->add()){
            return array('state'=>0,'msg'=>'添加黑名单失败！');
        }else{
            return array('state'=>1,'msg'=>'成功将分享人拉入黑名单！','data'=>array('id'=>$id));
        }
    }
}
?>