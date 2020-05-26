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
// | ModelName: 短信配置表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class SmsConfigModel extends Model{
	/**
     * 读取系统参数生成缓存文件
     */
    public function config_cache() {
        $config = array();
        $res = $this->where()->getField('name,value');
        foreach ($res as $key=>$val) {
        	$un_result=unserialize($val);
        	$config[$key] = $un_result ? $un_result : $val;
        }
        F('sms_config', $config);
        return $config;
    }
    /**
     * [get_cache 读取缓存]
     */
    public function get_cache(){
        if(false === $config = F('sms_config')){
            $config = $this->config_cache();
        }
        return $config;
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('sms_config', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('sms_config', NULL);
    }
}
?>