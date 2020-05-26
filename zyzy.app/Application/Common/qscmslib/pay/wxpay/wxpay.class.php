<?php
// +----------------------------------------------------------------------
// | 74CMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.74cms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | ModelName: 微信支付类
// +----------------------------------------------------------------------
require_once dirname(__FILE__) . '/WxPay.Data.php';
require_once dirname(__FILE__) . '/WxPay.NativePay.php';
require_once dirname(__FILE__) . '/WxPay.JsApiPay.php';
require_once dirname(__FILE__) . '/WxPay.MchPay.php';
require_once dirname(__FILE__) . '/WxPay.H5Pay.php';
require_once dirname(__FILE__) . '/WxPay.MchPackagePay.php';
class wxpay_pay{
	protected $_error = 0;
	protected $setting = array();
	public function __construct($data) {
		WxPayConfig::$appid = C('qscms_weixin_appid');
		WxPayConfig::$mchid = $data['partnerid'];
		WxPayConfig::$key = $data['ytauthkey'];
		WxPayConfig::$appsecret = C('qscms_weixin_appsecret');
		$this->setting = $data;
	}
	/**
	 * [微信支付订单二维码生成]
	 * @param  string $type   [description]
	 * @param  [type] $option [description]
	 * @return [type]         [description]
	 */
	public function dopay($option){
		$return = '';
		switch($option['pay_from']){
			case 'pc':
				$return = $this->_pay_from_pc($option);
				break;
			case 'wap':
				$return = $this->_pay_from_wap($option);
				break;
			case 'app':
				WxPayConfig::$appid = $this->setting['parameter1'];
				WxPayConfig::$appsecret = $this->setting['parameter2'];
				$return = $this->_pay_from_app($option);
				break;
		}
		return $return;
	}
	/**
	 * 网页版支付
	 */
	protected function _pay_from_pc($option){
		$wxpay = new WxPayUnifiedOrder();
		$notify = new NativePay();
		$wxpay->SetBody($option['ordbody']);//描述
		$wxpay->SetAttach("test");//回调附加参数
		$wxpay->SetOut_trade_no($option['oid']);//商户订单号
		$wxpay->SetTotal_fee($option['ordtotal_fee']*100);//支付金额
		//$wxpay->SetTotal_fee(0.01*100);//支付金额
		$wxpay->SetTime_start(date("YmdHis"));//交易起始时间
		$wxpay->SetTime_expire(date("YmdHis", time() + 600));//交易结束时间
		$wxpay->SetGoods_tag($option['ordsubject']);//商品标记
		$pay_resource = isset($option['pay_resource'])?$option['pay_resource']:'';
		$wxpay->SetNotify_url($option['site_dir'].'Home/Callback/wxpay/pay_resource/'.$pay_resource);//支付通知回调地址
		$wxpay->SetTrade_type("NATIVE");//交易类型
		$wxpay->SetProduct_id("123456789");
		$result = $notify->GetPayUrl($wxpay);
        return $result["code_url"];
	}
	/**
	 * 触屏支付
	 */
	protected function _pay_from_wap($option){
		if(isset($option['h5_pay']) && $option['h5_pay']==1){
			$h5pay = 1;
		}else{
			$h5pay = 0;
		}
		$wxpay = new WxPayUnifiedOrder();
		if($h5pay==1){
			$notify = new H5Pay();
		}else{
			$notify = new JsApiPay();
		}
		$wxpay->SetBody($option['ordbody']);//描述
		$wxpay->SetAttach("test");//回调附加参数
		$wxpay->SetOut_trade_no($option['oid']);//商户订单号
		$wxpay->SetTotal_fee($option['ordtotal_fee']*100);//支付金额
		//$wxpay->SetTotal_fee(0.01*100);//支付金额
		$wxpay->SetTime_start(date("YmdHis"));//交易起始时间
		$wxpay->SetTime_expire(date("YmdHis", time() + 600));//交易结束时间
		$wxpay->SetGoods_tag($option['ordsubject']);//商品标记
		$pay_resource = isset($option['pay_resource'])?$option['pay_resource']:'';
		$wxpay->SetNotify_url($option['site_dir'].'Home/Callback/wxpay/pay_resource/'.$pay_resource);//支付通知回调地址
		if($h5pay==1){
			$wxpay->SetTrade_type("MWEB");//交易类型
			$wxpay->SetSpbill_create_ip(get_client_ip());
			$order = WxPayApi::unifiedOrder($wxpay);//创建统一支付表单信息
			if($order['return_code']=='SUCCESS'){
				if($order['result_code']=='SUCCESS'){
					$url = $order['mweb_url'];
					$option['redirect_url'] && $url .= '&redirect_url='.urlencode($option['redirect_url']);
				}else{
					$this->_error = $order['err_code_des'];
					return false;
				}
			}else{
				$this->_error = $order['return_msg'];
				return false;
			}
			return $url;
		}else{
			$openId = !$option['openid']?$notify->GetOpenid():$option['openid'];
			$wxpay->SetOpenid($openId);//用户标识
			$wxpay->SetTrade_type("JSAPI");//交易类型
			$order = WxPayApi::unifiedOrder($wxpay);//创建统一支付表单信息
			$jsApiParameters = $notify->GetJsApiParameters($order);
			//获取共享收货地址js函数参数
			$editAddress = $notify->GetEditAddressParameters();
	        return array('jsApiParameters'=>$jsApiParameters,'editAddress'=>$editAddress);
		}
	}
	/**
	 * APP支付
	 */
	public function _pay_from_app($option){
		$aop = new WxPayApi();
		//$inputObj->total_fee='1';
		$wxpay = new WxPayUnifiedOrder();
		//$wxpayBase = new WxPayDataBase();
		$wxpay->SetOut_trade_no($option['oid']);//商户订单号
		$wxpay->SetTotal_fee($option['ordtotal_fee']*100);//支付金额
		//$wxpay->SetTotal_fee(0.01*100);
		$wxpay->SetTrade_type("APP");//交易类型
		$wxpay->SetBody($option['ordbody']);//描述
		// $wxpay->SetPackage('Sign=WXPay');
		$pay_resource = isset($option['pay_resource'])?$option['pay_resource']:'';
		$wxpay->SetNotify_url($option['site_dir'].'Home/Callback/wxpay/pay_resource/'.$pay_resource);//支付通知回调地址
		$response = $aop->unifiedOrder($wxpay);
		if($response['return_code'] == 'FAIL'){
			$this->_error = $response['return_msg'];
			return false;
		}
		$info = array();
		$info['appid'] = $response['appid'];
		$info['partnerid'] = $response['mch_id'];
		// $info['package'] = "prepay_id=" . $response['prepay_id'];
		$info['package'] = "Sign=WXPay";
		$info['noncestr'] = $response['nonce_str'];
		$info['timestamp'] = time();
		$info['prepayid'] = $response['prepay_id'];
		$info['sign'] = $aop->MakeSign($info);//生成签名
		//$response = $aop->getPrePayOrder($subject, $out_trade_no, $total_amount);
		return $info;
	}
	/**
	 * [payment 企业付款]
	 */
	public function payment($data){
		if(!$data['openid']){
			$this->_error = '请填写用户openid！';
			return false;
		}
		if(!$data['partner_trade_no']){
			$this->_error = '请填写订单号！';
			return false;
		}
		if($data['amount'] <= 0){
			$this->_error = '请填写订单金额！';
			return false;
		}
		$pay = new MchPay();
		// 用户openid
        //$data['openid']='oy2lbszXkgvlEKThrzqEziKEBzqU';
        // 商户订单号
        //$data['partner_trade_no']='test-'.time();
        // 校验用户姓名选项
        $data['check_name'] = 'NO_CHECK';
        // 企业付款金额  单位为分
        $data['amount'] = $data['amount'] * 100;
        // 企业付款描述信息
        $data['desc']='红包发放';
        // 调用接口的机器IP地址  自定义
        $data['spbill_create_ip'] = get_client_ip();
        // 收款用户姓名
        // $data['re_user_name']='Max wen';
        // 设备信息
        // $data['device_info']='dev_server';
        if(false !== $reg = $pay->postXmlSSL($data)) return $reg;
        $this->_error = $pay->getError();
        return false;
	}
    /**
     * [payment 现金红包]
     */
    public function red_package($data){
        if(!$data['re_openid']){
            $this->_error = '请填写用户openid！';
            return false;
        }
        if($data['total_amount'] <= 0){
            $this->_error = '请填写订单金额！';
            return false;
        }
        if(!$data['mch_billno']){
            $this->_error = '请填写订单号！';
            return false;
        }
        $pay = new MchPackagePay();
        // 用户openid
        //$data['openid']='oy2lbszXkgvlEKThrzqEziKEBzqU';
        // 商户订单号
        // $data['partner_trade_no']='test-'.time();
        // 企业付款金额  单位为分
        $data['total_amount'] = $data['total_amount'] * 100;
        // $data['total_amount'] = 100;
        $data['total_num'] = 1;

        // 企业付款描述信息
        // $data['desc']='红包发放';
        // 调用接口的机器IP地址  自定义
        $data['client_ip'] = get_client_ip();
        // 收款用户姓名
        // $data['re_user_name']='Max wen';
        // 设备信息
        // $data['device_info']='dev_server';
        if(false !== $reg = $pay->postXmlSSL($data)) return $reg;
        $this->_error = $pay->getError();
        return false;
    }
    public function getError(){
    	return $this->_error;
    }
}
?>