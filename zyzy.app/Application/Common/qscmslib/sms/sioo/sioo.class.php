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
// | ModelName: 希奥短信接口
// +----------------------------------------------------------------------
class sioo_sms{
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
		$data['uid'] = $this->setting['appkey'];
		$data['auth'] = md5($this->setting['secretKey']);
		//解析模板内容
		if($option['data']){
            foreach ($option['data'] as $key => $val) {
                $data['{'.$key.'}'] = $val;
            }
            $data['msg'] = strtr($option['tpl'],$data);
        }else{
            $data['msg'] = $option['tpl'];
        }
        $data['msg'] = iconv('UTF-8','GB2312//IGNORE',$data['msg']);
		$data['mobile'] = $option['mobile'];
		$data['expid'] = $this->setting['signature'];
		$url='http://210.5.158.31/hy?'.http_build_query($data);
		$f=$this->Get($url);
		if ($f==0)
		{
			return true;
		}
		else
		{
			$this->_error = $f.'短信发送失败，请联系服务商！';
			return false;
		}
	}
	protected function Get($url)
	{
		if(function_exists('file_get_contents'))
		{
			$file_contents = file_get_contents($url);
		}
		else
		{
			$ch = curl_init();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		}
		return $file_contents;
	} 
	public function getError(){
		return $this->_error;
	}
}
?>