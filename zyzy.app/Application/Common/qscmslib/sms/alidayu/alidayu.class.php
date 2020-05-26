<?php
/**
 * 阿里大鱼短信类
 */
include "TopSdk.php";
require_once dirname(__FILE__) . '/top/TopClient.php';
require_once dirname(__FILE__) . '/top/request/AlibabaAliqinFcSmsNumSendRequest.php';
require_once dirname(__FILE__) . '/top/ResultSet.php';
require_once dirname(__FILE__) . '/top/RequestCheckUtil.php';
require_once dirname(__FILE__) . '/top/TopLogger.php';
class alidayu_sms{
	protected $extend = '';
	protected $smsType = 'normal';
	protected $client;
	public function __construct($setting){
		$this->setting = $setting;
		$this->client = $this->setClient();
	}
	/**
	 * 发送模板短信
	 * @param    string     $type 短信通道
	 * @param    array      $option['mobile':手机号码集合,用英文逗号分开,'tpl':模板ID(阿里大鱼模板ID),'data':'模板所需数据']
	 * @return   boolean
	 */
	public function sendTemplateSMS($type,$option){
		unset($option['data']['sitedomain']);
		$req = new AlibabaAliqinFcSmsNumSendRequest;
		$req->setExtend($this->extend);
		$req->setSmsType($this->smsType);
		$req->setSmsFreeSignName($this->setting['signature']);
		$req->setSmsParam(json_encode($option['data']));
		$req->setRecNum($option['mobile']);
		$req->setSmsTemplateCode($option['tplId']);
		$resp = $this->client->execute($req);
		if($resp->result->success=="true"){
			return true;
		}else{
			$resp = (array)$resp;
			$this->_error = $resp['sub_msg']?:$resp['msg'];
			return false;
		}
	}
	protected function setClient(){
		$c = new TopClient;
		$c->appkey = $this->setting['appkey'];
		$c->secretKey = $this->setting['secretKey'];
		return $c;
	}
	public function getError(){
		return $this->_error;
	}
}
?>