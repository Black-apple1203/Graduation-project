<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://74cms.com All rights reserved.
// +----------------------------------------------------------------------
/**
 * 自动识别触屏端
 * @category   Extend
 * @package  Extend
 * @subpackage  Behavior
 */
namespace Common\Behavior;
class IsMobileBehavior{
    public function run(&$params) {
        if(false === $apply = F('apply_list')) $apply = D('Apply')->apply_cache();
        if(!$apply['Mobile'])return;
        $is_mobile = $this->is_mobile_request();
        if($is_mobile){
            C('PLATFORM','mobile');
            return;
        }
        // // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        // if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        //     C('PLATFORM','mobile');
        //     return;
        // }

        // //此条摘自TPM智能切换模板引擎，适合TPM开发
        // if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT']){
        //     C('PLATFORM','mobile');
        //     return;
        // }
        // //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        // if (isset ($_SERVER['HTTP_VIA'])){
        //     //找不到为flase,否则为true
        //     stristr($_SERVER['HTTP_VIA'], 'wap') && C('PLATFORM','mobile');
        //     return;
        // }
        // //判断手机发送的客户端标志,兼容性有待提高
        // if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        //     $clientkeywords = array(
        //             'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        //     );
        //     //从HTTP_USER_AGENT中查找手机浏览器的关键字
        //     if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
        //         C('PLATFORM','mobile');
        //         return;
        //     }
        // }
        // //协议法，因为有可能不准确，放到最后判断
        // if (isset ($_SERVER['HTTP_ACCEPT'])) {
        //     // 如果只支持wml并且不支持html那一定是移动设备
        //     // 如果支持wml和html但是wml在html之前则是移动设备
        //     if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
        //         C('PLATFORM','mobile');
        //         return;
        //     }
        // }
        // return;
    }
    private function is_mobile_request()   
    {    
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';    
        $mobile_browser = '0';    
        if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) $mobile_browser++;    
        if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))     $mobile_browser++;    
        if(isset($_SERVER['HTTP_X_WAP_PROFILE']))     $mobile_browser++;    
        if(isset($_SERVER['HTTP_PROFILE']))     $mobile_browser++;    
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));    
        $mobile_agents = array(       'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',       'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',       'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',       'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',       'newt','noki','oper','palm','pana','pant','phil','play','port','prox',       'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',       'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',       'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',       'wapr','webc','winw','winw','xda','xda-'     );    if(in_array($mobile_ua, $mobile_agents))     $mobile_browser++;    
        if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)     $mobile_browser++;    
        // Pre-final check to reset everything if the user is on Windows    
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)     $mobile_browser=0;    
        // But WP7 is also Windows, with a slightly different characteristic    
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)     $mobile_browser++;    
        if($mobile_browser>0)     
            return true;   
        else   
            return false; 
    }
}