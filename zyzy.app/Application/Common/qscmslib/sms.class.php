<?php
/**
 * 短信类
 *
 * @author andery
 */
namespace Common\qscmslib;
class sms
{
    protected $_error;
    protected $_setting = array();
    protected $_type = 'qscms';
    protected $_smsSendType = array('captcha','notice','other');
    public function __construct($name) {
    	$this->_type = $name ? $name : C('qscms_sms_default_service');
    	if(false === $sms_list = F('sms_list')){
			$sms_list = D('Sms')->sms_cache();
		}
		$this->_setting = unserialize($sms_list[$this->_type]['config']);
        include_once QSCMSLIB_PATH . "sms/{$this->_type}/{$this->_type}.class.php";
        $class = $this->_type . '_sms';
        $this->_sms = new $class($this->_setting);
    }
    public function sendTemplateSMS($type='captcha',&$option){
        if(!$this->_setting){
            $this->_error = '请配置短信参数！';
            return false;
        }
    	if(!in_array($type,$this->_smsSendType)){
    		$this->_error = '请选择正确的短信发送类型！';
    		return false;
    	}
    	if(!$option['mobile']){
    		$this->_error = '请填写手机号码！';
    		return false;
    	}
    	if(!trim($option['tpl'])){
    		$this->_error = '请选择短信模板！';
    		return false;
    	}
    	if(!$this->_mobileBreak($option['mobile'])){
    		$this->_error = '手机号格式不合法！';
    		return false;
    	}
    	if(false === $result = $this->_sms->sendTemplateSMS($type,$option)){
    		$this->_error = $this->_sms->getError();
    		return false;
    	}
    	return $result;
    }
    /**
	 * [手机批量验证]
	 * @param  [string] $data ['15865441236,13652415865']
	 * @return [boolean]
	 */
	protected function _mobileBreak($data=''){
		$mobile = explode(',',$data);
		foreach ($mobile as $val) {
			if(false === fieldRegex($val,'mobile')) return false;
		}
		return true;
	}
    public function getError() {
        return $this->_error;
    }
}