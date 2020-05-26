<?php
/*
	tenpay 
*/
require_once dirname(__FILE__) . '/PayRequestHandler.class.php';
require_once dirname(__FILE__) . '/PayResponseHandler.class.php';
class tenpay_pay
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
		$tenpay_config = array(
			'bargainor_id' =>$this->setting['partnerid'],
	        'key'=>$this->setting['ytauthkey'],
		);
		/**************************请求参数**************************/
		$bargainor_id = trim($tenpay_config['partnerid']);////商户编号
		$key=trim($tenpay_config['ytauthkey']);//MD5密钥
		$return_url= $data['site_dir'].'callback/tenpay_return_url';
		//date_default_timezone_set(PRC);
	    $strDate = date("Ymd");
	    $strTime = date("His");
	    $randNum = rand(1000, 9999);//4位随机数
	    $strReq = $strTime . $randNum;//10位序列号,可以自行调整。	
	    $transaction_id = $bargainor_id . $strDate . $strReq;/* 财付通交易单号，规则为：10位商户号+8位时间（YYYYmmdd)+10位流水号 */
		$sp_billno = $data['oid'];//订单号 商家订单号,长度若超过32位，取前32位。财付通只记录商家订单号，不保证唯一。
		$total_fee =intval($data['ordtotal_fee'])*100;/* 商品价格（包含运费），以分为单位 */
		$desc = $data['ordbody'];
		/* 创建支付请求对象 */
	    $reqHandler = new PayRequestHandler();
	    $reqHandler->init();
	    $reqHandler->setKey($key);
		$reqHandler->setParameter("bargainor_id", $bargainor_id);			//商户号
		$reqHandler->setParameter("sp_billno", $sp_billno);					//商户订单号
		$reqHandler->setParameter("transaction_id", $transaction_id);		//财付通交易单号
		$reqHandler->setParameter("total_fee", $total_fee);					//商品总金额,以分为单位
		$reqHandler->setParameter("return_url", $return_url);				//返回处理地址
		$reqHandler->setParameter("desc", $desc);	//商品名称
		$reqHandler->setParameter("spbill_create_ip", get_client_ip());
		$reqUrl = $reqHandler->getRequestURL();
		// $def_url  ="<a href=\"".$reqUrl."\" target=\"_blank\"><img src=\"".C('qscms_site_template')."images/25.gif\" border=\"0\"/></a>";
		// $def_url  ="<input type=\"button\" class=\"but130lan intrgration_but\" value=\"确认支付\"  onclick=\"javascript:window.open('".$reqUrl."')\"/>";
		redirect($reqUrl);
	}

	/*
	验证操作
	*/
	public function tenpayNotify()
	{
		$key = $this->setting['ytauthkey'];
		/* 创建支付应答对象 */
		$resHandler = new PayResponseHandler();
		$resHandler->setKey($key);
		if($resHandler->isTenpaySign())
		{
			//商户单号
			$sp_billno = $resHandler->getParameter("sp_billno");
			//财付通交易单号
			$transaction_id = $resHandler->getParameter("transaction_id");
			//金额,以分为单位
			$total_fee = $resHandler->getParameter("total_fee");
			$pay_result = $resHandler->getParameter("pay_result");
			if( "0" == $pay_result ) 
			{
				return $sp_billno;
			}
			else
			{
				return false;
				$this->_error = "验证失败！";
			}
		}
		else
		{
			return false;
			$this->_error = "验证失败！";
		}
	}
	public function getError(){
		return $this->_error;
	}
}
?>
