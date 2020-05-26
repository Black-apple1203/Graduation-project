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
// | ModelName: 叮咚鱼短信接口
// +----------------------------------------------------------------------
class dingdongyu_sms{
	protected $_error = 0;
	protected $setting = array();
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
		$send_arr['apikey'] = $this->setting['appkey'];
		//解析模板内容
		if($option['data']){
            foreach ($option['data'] as $key => $val) {
                $data['{'.$key.'}'] = $val;
            }
            $data['msg'] = strtr($option['tpl'],$data);
        }else{
            $data['msg'] = $option['tpl'];
        }
        $send_arr['content'] = '【'.$this->setting['signature'].'】'.$data['msg'];
		$send_arr['mobile'] = $option['mobile'];
		$ch = curl_init();
		/* 设置验证方式 */
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
		/* 设置返回结果为流 */
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		/* 设置超时时间*/
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		/* 设置通信方式 */
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$this->send_yzm($ch,$send_arr);
		$result = json_decode($json_data,true);
		curl_close($ch);
		if ($result['code']==1)
		{
			return true;
		}
		else
		{
			$this->_error = $result['code'].'短信发送失败，请联系服务商！';
			return false;
		}
	}
	public function getError(){
		return $this->_error;
	}
	//验证码
	protected function send_yzm($ch,$data){
	    curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v1/sms/sendyzm');
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    return curl_exec($ch);
	}
}
?>