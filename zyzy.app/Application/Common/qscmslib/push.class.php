<?php
/**
 * 推送技术
 */
namespace Common\qscmslib;
class push{
	protected $_type = 'jpush';
	protected $_setting = array();
	protected $_error = '';
	public function __construct() {
		//导入接口文件
		include_once QSCMSLIB_PATH . 'push/' . $this->_type . '/' . $this->_type . '.class.php';
		$om_class = $this->_type . '_push';
		$this->_push = new $om_class(C('qscms_push_config'));
	}
	/**
	 * [sendPush 发送通知]
	 */
	public function sendPush($option){
		if(false === $reg = $this->_push->sendPush($option)) $this->_error = $this->_push->getError();
		return $reg;
	}
	public function getError(){
		return $this->_error;
	}
}
?>