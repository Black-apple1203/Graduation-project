<?php
/*
	alipay 
*/
require_once dirname(__FILE__) . '/Corefunction.php';
require_once dirname(__FILE__) . '/Md5function.php';
require_once dirname(__FILE__) . '/Notify.php';
require_once dirname(__FILE__) . '/Submit.php';
class alipay_pay
{
	protected $_error = 0;
	protected $setting = array();
	public function __construct($setting) {
		$this->setting = $setting;
	}
	/*
		支付操作
	*/
    public function dopay($data)
	{	
		$alipay_config = array(
			'partner' =>$this->setting['partnerid'],   //这里是你在成功申请支付宝接口后获取到的PID；
	        'key'=>$this->setting['ytauthkey'],		   //这里是你在成功申请支付宝接口后获取到的Key
	        'sign_type'=>strtoupper('MD5'),
	        'input_charset'=> strtolower('utf-8'),
	        'cacert'=> getcwd().'\\cacert.pem',
	        'transport'=> 'http'
		);
		$parameter = '';
		switch($data['pay_from']){
			case 'pc':
				$parameter = $this->_pay_from_pc($data,$alipay_config);
				break;
			case 'wap':
				$parameter = $this->_pay_from_wap($data,$alipay_config);
				break;
			case 'app':
				$alipay_config['appid'] = $this->setting['parameter2'];
		        $alipay_config['rsaPrivateKey'] = $this->setting['parameter3'];
		        $alipay_config['alipayrsaPublicKey'] = $this->setting['parameter4'];
				return $this->_pay_from_app($data,$alipay_config);
		}
	    //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;
	}
	/**
	 * 网页版支付
	 */
	protected function _pay_from_pc($data,$alipay_config){
		/**************************请求参数**************************/
        $payment_type = "1"; //支付类型 //必填，不能修改
       	$notify_url = $data['site_dir'].'callback/alipay_notify_url';
        $return_url = $data['site_dir'].'?m=Home&c=callback&a=alipay_return_url';
        $seller_email = $this->setting['parameter1'];//卖家支付宝帐户必填
        $out_trade_no = $data['oid'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $data['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $data['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
        $body = $data['ordbody'];  //订单描述 通过支付页面的表单进行传递
        $show_url = $data['ordshow_url'];  //商品展示地址 通过支付页面的表单进行传递
        $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
        $exter_invoke_ip = get_client_ip(); //客户端的IP地址 
        /************************************************************/
        //构造要请求的参数数组，无需改动
	    $parameter = array(
	        "service" => "create_direct_pay_by_user",
	        "partner" => trim($alipay_config['partner']),
	        "payment_type"    => $payment_type,
	        "notify_url"    => $notify_url,
	        "return_url"    => $return_url,
	        "seller_email"    => $seller_email,
	        "out_trade_no"    => $out_trade_no,
	        "subject"    => $subject,
	        "total_fee"    => $total_fee,
	        "body"            => $body,
	        "show_url"    => $show_url,
	        "anti_phishing_key"    => $anti_phishing_key,
	        "exter_invoke_ip"    => $exter_invoke_ip,
	        "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
	        );
	   	return $parameter;
	}
	/**
	 * 触屏支付
	 */
	protected function _pay_from_wap($data,$alipay_config){
		/**************************请求参数**************************/
        $payment_type = "1"; //支付类型 //必填，不能修改
       	$notify_url = $data['site_dir'].'callback/alipay_notify_url';
        $return_url = $data['site_dir'].'?m=Home&c=callback&a=alipay_return_url';
        $seller_email = $this->setting['parameter1'];//卖家支付宝帐户必填
        $out_trade_no = $data['oid'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $data['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $data['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
        $body = $data['ordbody'];  //订单描述 通过支付页面的表单进行传递
        $show_url = $data['ordshow_url'];  //商品展示地址 通过支付页面的表单进行传递
        $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
        $exter_invoke_ip = get_client_ip(); //客户端的IP地址 
        /************************************************************/
        //构造要请求的参数数组，无需改动
	    $parameter = array(
			"service"       => "alipay.wap.create.direct.pay.by.user",
			"partner"       => trim($alipay_config['partner']),
			"seller_id"  => $seller_email,
			"payment_type"	=> $payment_type,
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url,
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
			"out_trade_no"	=> $out_trade_no,
			"subject"	=> $subject,
			"total_fee"	=> $total_fee,
			"show_url"	=> $show_url,
			"app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
			"body"	=> $body,
			"anti_phishing_key"    => $anti_phishing_key,
	        "exter_invoke_ip"    => $exter_invoke_ip
			//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
	        //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。
		);
	   	return $parameter;
	}

	/*
	验证操作
	*/
	public function alipayNotify()
	{
		$alipay_config = array(
			'partner' =>$this->setting['partnerid'],
	        'key'=>$this->setting['ytauthkey'],		   
	        'sign_type'=>strtoupper('MD5'),
	        'input_charset'=> strtolower('utf-8'),
	        'cacert'=> getcwd().'\\cacert.pem',
	        'transport'=> 'http',
		);
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {
			return true;
		}else
		{
			$this->_error = "验证失败！";
			return false;
		}
	}
	/*
	验证操作
	*/
	public function alipayNotifyReturn()
	{
		$alipay_config = array(
			'partner' =>$this->setting['partnerid'],
	        'key'=>$this->setting['ytauthkey'],		   
	        'sign_type'=>strtoupper('MD5'),
	        'input_charset'=> strtolower('utf-8'),
	        'cacert'=> getcwd().'\\cacert.pem',
	        'transport'=> 'http',
		);
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {
			return true;
		}else
		{
			$this->_error = "验证失败！";
			return false;
		}
	}
	/**
	 * [_pay_from_app APP支付]
	 */
	public function _pay_from_app($data,$alipay_config){
		if(!$alipay_config['appid'] || !$alipay_config['rsaPrivateKey'] || !$alipay_config['alipayrsaPublicKey']){
			$this->_error = "请配置支付宝商户信息！";
			return false;
		}
		$out_trade_no = $data['oid'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $data['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $data['ordtotal_fee'];//$data['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
        $body = $data['ordbody'];  //订单描述 通过支付页面的表单进行传递

		$aop = new \Common\qscmslib\pay\alipay\AppPay\aop\AopClient();
		$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
		$aop->appId = $alipay_config['appid'];
		//请填写开发者私钥去头去尾去回车，一行字符串
		$aop->rsaPrivateKey = $alipay_config['rsaPrivateKey'];
		$aop->format = "json";
		$aop->charset = "UTF-8";
		$aop->signType = "RSA2";
		//请填写支付宝公钥，一行字符串
		$aop->alipayrsaPublicKey = $alipay_config['alipayrsaPublicKey'];
		//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		$request = new \Common\qscmslib\pay\alipay\AppPay\aop\request\AlipayTradeAppPayRequest();
		//SDK已经封装掉了公共参数，这里只需要传入业务参数
		$bizcontent = "{\"body\":\"{$body}\"," 
						. "\"subject\": \"{$subject}\","
						. "\"out_trade_no\": \"{$out_trade_no}\","
						. "\"timeout_express\": \"30m\"," //该笔订单允许的最晚付款时间 30M=30分钟
						. "\"total_amount\": \"{$total_fee}\","
						//. "\"product_code\":\"QUICK_MSECURITY_PAY\"" 销售产品码，可选
						. "}";
		$request->setNotifyUrl($data['site_dir'].'callback/alipay_notify_url_app');
		$request->setBizContent($bizcontent);
		//这里和普通的接口调用不同，使用的是sdkExecute
		$response = $aop->sdkExecute($request);
		//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
		//echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
		return $response;
	}
	public function getError(){
		return $this->_error;
	}
}
?>
