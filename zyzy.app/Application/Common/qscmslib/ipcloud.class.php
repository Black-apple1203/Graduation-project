<?php
/**
 * 生成ip类
 *
 * @author andery
 */
namespace Common\qscmslib;
class ipcloud
{
    public function ip(){
		if(empty($ip)) $ip = get_client_ip();
		$location['ip'] = gethostbyname($ip);   // 将输入的域名转化为IP地址
        $ips = explode(':',$location['ip']);
		$l_ip=$ips[0];
		$url=array(
		'baidu'=>'http://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query='.$l_ip.'&co=&resource_id=6006&oe=utf8',//百度
		'three'=>'http://ip.360.cn/IPQuery/ipquery?ip='.$l_ip.'',//360
		'fengtalk'=>'http://ip.fengtalk.com/ip/?ip='.$l_ip.'',
		);
		$type=array_rand($url,1);
		$opts = array('http' =>
			array(
			'method'  => 'GET',
			'timeout' => 3
			)
		);
		$context  = stream_context_create($opts);
		$reg = @file_get_contents($url[$type],false,$context);
		if($reg!=null || $reg!==false){
			$reg = json_decode($reg, true);
			switch($type){
				case 'baidu':
				$city=$reg['data'][0];
				$rst['country']=$city['location'];
				break;
				case 'three':
				$city=$reg['data'];
				$rst['country']=$city;
				break;
				case 'fengtalk':
				$rst['country']=$reg['cityname'];
				break;
			}
			if($rst['country']){
				return $rst;
			}else{
				return false;
			}
		}
		return false;
    }
}