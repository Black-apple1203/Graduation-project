<?php
namespace Common\Model;
use Think\Model;
class SyslogModel extends Model{
	protected $_validate = array(
		array('l_type,l_type_name,l_str','identicalNull','',0,'callback'),
	);
	protected $_auto = array ( 
		array('l_time','time',1,'function'),
		array('l_ip','get_client_ip',1,'function'),
		array('l_address','get_address',1,'callback'),
		array('l_page','request_url',1,'callback'),
	);
	/*
		根据ip 获取地址
	*/
	protected function get_address(){
		$Ip = new \Common\ORG\IpLocation('UTFWry.dat');
		$rst = $Ip->getlocation(); 
		return $rst['country'];
	}
	/*
		日志获取 错误页面
	*/
	protected function request_url()
	{
	  	if (isset($_SERVER['REQUEST_URI']))     
	    {        
	   	 $url = $_SERVER['REQUEST_URI'];    
	    }
		else
		{    
			  if (isset($_SERVER['argv']))        
				{           
				$url = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];      
				}         
			  else        
				{          
				$url = $_SERVER['PHP_SELF'] .'?'.$_SERVER['QUERY_STRING'];
				}  
	    }    
	    return urlencode($url); 
	}
}
?>