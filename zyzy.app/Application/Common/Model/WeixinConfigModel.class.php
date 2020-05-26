<?php
namespace Common\Model;
use Think\Model;
class WeixinConfigModel extends Model{
	/**
	 * 读取系统参数生成缓存文件
	 */
	public function config_cache() {
		$config = array();
		$res = $this->where()->getField('alias,value,template_id');
		foreach ($res as $key=>$val) {
			$un_result=unserialize($val);
			$config[$key] = $un_result ? $un_result : $val;
		}
		F('weixin_config', $config);
		return $config;
	}
	// 读取微信配置
	public function get_cache(){
		if(false === $weixin_config = F('weixin_config')) $weixin_config = $this->config_cache();
		return $weixin_config;
	}
	/**
	 * [config_list_cache 微信配置列表]
	 */
	public function config_list_cache(){
		$config_list = $this->field('id,alias,name,value,info')->select();
		F('weixin_config_list',$config_list);
		return $config_list;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('weixin_config', NULL);
        F('weixin_config_list',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('weixin_config', NULL);
        F('weixin_config_list',NULL);
    }
}
?>