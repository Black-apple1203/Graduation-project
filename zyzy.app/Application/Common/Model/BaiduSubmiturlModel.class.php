<?php 
namespace Common\Model;
use Think\Model;
class BaiduSubmiturlModel extends Model
{
	/**
     * 读取系统参数生成缓存文件
     */
    public function config_cache() {
        $config = array();
        $res = $this->getField('id,name,value');
        foreach ($res as $key=>$val) {
        	$config[$val['name']] = $val['value'];
        }
        F('baidu_submiturl', $config);
        return $config;
    }
    // 读取邮件配置
    public function get_cache(){
        if(false === $points = F('baidu_submiturl')){
            $points = $this->config_cache();
        }
        return $points;
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('baidu_submiturl', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('baidu_submiturl', NULL);
    }
}
?>