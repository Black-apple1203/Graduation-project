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
// | ModelName: 74cms短信类
// +----------------------------------------------------------------------
class qscms_sms{
	protected $_error = 0;
	protected $setting = array();
	protected $_base_url = 'https://smsapi.74cms.com?noencode=1&';//基础类短信请求地址
	protected $_notice_url = 'https://smsapi.74cms.com?noencode=1&';//通知类短信请求地地址
	protected $_captcha_url = 'https://smsapi.74cms.com?noencode=1&';//验证码类短信请求地址
	protected $_other_url = 'https://smsapi.74cms.com?market=1&noencode=1&';//其它类短信请求地址
	public function __construct($setting) {
		$this->setting = $setting;
	}
	/**
	 * 发送模板短信
	 * @param    string     $type 短信通道 手机号码集合,用英文逗号分开
	 * @param    array      $option['mobile':手机号码集合,用英文逗号分开,'content':短信内容]
	 * @return   boolean
	 */
	public function sendTemplateSMS($type='captcha',$option){
		$data['sms_name'] = $this->setting['appkey'];
		$data['sms_key'] = $this->setting['secretKey'];
		//解析模板内容
		if($option['data']){
            foreach ($option['data'] as $key => $val) {
                $data['{'.$key.'}'] = $val;
            }
            $data['content'] = strtr($option['tpl'],$data);
        }else{
            $data['content'] = $option['tpl'];
        }
        //客户IP
       	$data['client_ip'] = $_SERVER['REMOTE_ADDR'];
        //转换编码
        /*foreach ($data as $key => $val) {
			$data[$key] = iconv('UTF-8','GB2312//IGNORE',$val);
		}*/
		$name = '_'.$type.'_url';
		$url = $this->$name.http_build_query($data);
		//遍历发送
        $mobile = explode(',',$option['mobile']);
        foreach ($mobile as $key => $val) {
        	if(false === $this->_https_request($url.'&mobile='.$val)) return false;
        }
		return true;
	}
	protected function _https_request($url,$data = null){
		if(function_exists('curl_init')){
			$curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($curl, CURLOPT_USERAGENT, _USERAGENT_);
			curl_setopt($curl, CURLOPT_REFERER,_REFERER_);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		    curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		    if (!empty($data)){
		        curl_setopt($curl, CURLOPT_POST, 1);
		        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		    }
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    $output = curl_exec($curl);
		    curl_close($curl);
		    if($output!='success'){
		    	$this->_error = '触发业务控制，错误码：'.$output;
		    	return false;
		    }
		    return $output;
		}else{
			$this->_error = '短信发送失败，请开启curl服务！';
			return false;
		}
	}
	public function getError(){
		return $this->_error;
	}
}
?>