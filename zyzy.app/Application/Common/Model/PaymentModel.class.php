<?php
namespace Common\Model;
use Think\Model;
class PaymentModel extends Model{
	protected $_validate = array(
		array('typename,byname,p_introduction,notes,partnerid,ytauthkey,parameter1','identicalNull','',0,'callback'),
		array('typename','identicalLength_15','',0,'callback'),
		array('byname,parameter1,parameter2','identicalLength_50','',0,'callback'),
		array('ytauthkey','identicalLength_100','',0,'callback'),
		array('partnerid','identicalLength_80','',0,'callback'),
		array('fee','identicalLength_6','',0,'callback'),
	);
	protected $_auto = array (
		array('listorder',50),
		array('p_install',1),
		array('fee',0),
	);
	protected function identicalLength_6($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=6) return 'payment_length_error_'.$key;
		}
		return true;
	}
	protected function identicalLength_15($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=15) return 'payment_length_error_'.$key;
		}
		return true;
	}
	protected function identicalLength_50($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=50) return 'payment_length_error_'.$key;
		}
		return true;
	}
	protected function identicalLength_80($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=80) return 'payment_length_error_'.$key;
		}
		return true;
	}
	protected function identicalLength_100($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=100) return 'payment_length_error_'.$key;
		}
		return true;
	}
	/**
     * 读取系统参数生成缓存文件
     */
    public function config_cache() {
        $config = array();
        $res = $this->where(array('p_install'=>2))->getField('typename,byname,partnerid,ytauthkey,fee,parameter1,parameter2,parameter3,parameter4');
        foreach ($res as $key=>$val) {
        	$config[$key] = $un_result ? $un_result : $val;
        }
        F('payment', $config);
        return $config;
    }
    // 读取支付配置
    public function get_cache(){
        if(false===F('payment')) return $this->config_cache();
        return F('payment');
    }
    public function get_payment_info($typename,$name=false){
		if($typename == 'points') return C('qscms_points_byname').'兑换';
		$val=$this->where(array('typename'=>$typename,'p_install'=>2))->find();
		if($name) return $val['byname'];
		return $val;
	}
    /*
		支付操作
		@data
		payFrom =pc 电脑端还是 手机端 'pc','app','wap'
		type = alipay;
		oid = 订单id
		ordsubject = 订单名称
		ordtotal_fee = 订单金额
		ordbody = 订单描述

    */	
    public function pay($data){
    	$pay = new \Common\qscmslib\pay($data['type']);
    	$data['site_dir'] = C('qscms_site_domain').C('qscms_site_dir');
        $rst = $pay->cash_with_order($data['payFrom'],$data);
        if($rst === false) return array('state'=>0,'msg'=>$pay->getError());
        return array('state'=>1,'data'=>$rst);
    }
	/**
	 * 后台有更新则删除缓存
	 */
    protected function _before_write($data, $options) {
        F('payment', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('payment', NULL);
    }
}
?>