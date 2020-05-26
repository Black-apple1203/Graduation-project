<?php
/**
 * 支付类
 *
 * @author 
 */
namespace Common\qscmslib;
class pay
{
    protected $_error;
    protected $_setting = array();
    protected $_type;
    protected $_payFrom = array('pc','app','wap');
    public function __construct($name) {
        $this->_type = $name ? $name : 'alipay'; //C('qscms_pay_default_type')
        $pay_type = D('Payment')->get_cache();
        $this->_setting = $pay_type[$this->_type];
        include QSCMSLIB_PATH . "pay/{$this->_type}/{$this->_type}.class.php";
        $class = $this->_type . '_pay';
        $this->_pay = new $class($this->_setting);
    }
    /*
        $option
        oid 订单号
        ordsubject 订单名称
        ordtotal_fee 订单金额
        ordbody 订单描述
        site_dir 网站域名
    */
    public function cash_with_order($type='pc',$option){
    	if(!in_array($type,$this->_payFrom)){
    		$this->_error = '请选择正确的付款途径！';
    		return false;
    	}
        $option['pay_from'] = $type;
        if(!$option['site_dir'])
        {
            $this->_error = '请填回调前缀！';
            return false;
        }
        if(!$option['oid'])
        {
            $this->_error = '请填写订单号！';
            return false;
        }
        if(!$option['oid'])
        {
            $this->_error = '订单号错误！';
            return false;
        }
        if(!$option['ordsubject'])
        {
            $this->_error = '请填写订单名称！';
            return false;
        }
        if(!$option['ordtotal_fee'])
        {
            $this->_error = '请填订单金额！';
            return false;
        }
        if(!$option['ordbody'])
        {
            $this->_error = '请填写订单描述！';
            return false;
        }
        $result = $this->_pay->dopay($option);
    	// if(false === $result = $this->_sms->sendTemplateSMS($type,array('mobile'=>$option['mobile'],'content'=>$content))){
    	// 	$this->_error = $this->_sms->getError();
    	// 	return false;
    	// }
        if($result === false) $this->_error = $this->_pay->getError(); 
    	return $result;
    }
    public function alipayNotify(){
        return $this->_pay->alipayNotify();
    }
    public function alipayNotifyReturn(){
        return $this->_pay->alipayNotifyReturn();
    }
    /**
     * [payment 企业付款]
     */
    public function payment($data){
        return $this->_pay->payment($data);
    }
    public function getError() {
        return $this->_error;
    }
}