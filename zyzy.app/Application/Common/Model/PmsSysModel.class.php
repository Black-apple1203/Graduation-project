<?php
namespace Common\Model;
use Think\Model;
class PmsSysModel extends Model{
	protected $_validate = array(
		array('message','identicalNull','',0,'callback'),
	);
	protected $_auto = array ( 
		array('dateline','time',1,'function'),
		array('spms_usertype',0),
		array('spms_type',1),
		array('replyuid',0),
	);
	/**
	 * [sys_cache 缓存当前系统消息最新时间]
	 */
	public function sys_cache(){
		if($data = $this->where($where)->order('dateline desc')->getfield('dateline',true)){
			$sysMgs = array('time'=>$data[0],'count'=>count($data));
			F('sysMsg',$sysMgs);
			return $sysMgs;
		}
		return array('time'=>0,'count'=>0);
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('sysMsg',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        $this->_before_write();
    }
}
?>