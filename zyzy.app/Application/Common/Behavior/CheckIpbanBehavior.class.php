<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://74cms.com All rights reserved.
// +----------------------------------------------------------------------
/**
 * IP过滤
 * @category   Extend
 * @package  Extend
 * @subpackage  Behavior
 */
namespace Common\Behavior;
class CheckIpbanBehavior{
    public function run(&$params){
        header('Content-Type:text/html; charset=utf-8');
        if(false === $config = F('config')){
            $config = D('Config')->config_cache();
        }
        if (!$config['qscms_filter_ip']) return;
        $ip = get_client_ip();
        $ip_s =explode(':',$ip);
        $ips = explode('|',$config['qscms_filter_ip']);
        in_array($ip_s[0],$ips) && exit($config['qscms_filter_ip_tips']);
    }
}