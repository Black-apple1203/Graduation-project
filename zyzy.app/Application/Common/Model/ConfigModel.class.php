<?php 
namespace Common\Model;
use Think\Model;
class ConfigModel extends Model{
	/**
     * 读取系统参数生成缓存文件
     */
    public function config_cache() {
        $config = array();
        $res = $this->where()->getField('name,value');
        foreach ($res as $key=>$val) {
            $un_result=unserialize($val);
        	$config['qscms_'.$key] = $un_result ? $un_result : $val;
            if(preg_match('/(http||https):\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$config['qscms_'.$key])){
                $config['qscms_'.$key] = htmlspecialchars_decode($config['qscms_'.$key],ENT_QUOTES);
            }
        }
        F('config', $config);
        return $config;
    }
    /**
     * [sub_domain 子域名生成]
     */
    public function sub_domain(){
        if(false === $apply = F('apply_list')) $apply = D('Apply')->apply_cache();
        if($apply['Mobile'] && C('qscms_wap_domain')){
            $m = str_replace('http://','',C('qscms_wap_domain'));
            $m = str_replace('https://','',$m);
            $m && $domain[$m] = 'Mobile';
        }
		$sub_arr = M('Subsite')->where(array('s_display'=>1))->getField('s_m_domain',true);
		if($sub_arr){
			foreach ($sub_arr as $val) {
				if($val){
					$domain[$val] = 'Mobile';
				}
			}
		}
        return $domain ?:array();
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('config', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('config', NULL);
    }
    protected function _after_update($data,$options){
        C('qscms_'.$options['where']['name'],$data['value']);
    }
}
?>