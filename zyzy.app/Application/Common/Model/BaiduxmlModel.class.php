<?php
/**
 * 百度开放平台
 */
namespace Common\Model;
use Think\Model;
class BaiduxmlModel extends Model{
    /**
     * 读取其它分类(全部)参数生成缓存文件
     */
    public function baiduxml_cache() {
        $baiduxml = array();
        $baiduxmlData = $this->field('id,name,value')->select();
        foreach ($baiduxmlData as $key=>$val){
            $baiduxml[$val['name']] = $val['value'];
        }
        F('baiduxml', $baiduxml);
        return $baiduxml;
    }
    /**
     * [get_baiduxml_cache 读取缓存]
     * @param  string $type [单一分类名称]
     * @return array       [分类集]
     */
    public function get_baiduxml_cache()
    {
        if(false === $baiduxml = F('baiduxml')){
            $baiduxml = $this->baiduxml_cache();
        }
        return $baiduxml;
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('baiduxml', NULL);
    }
}