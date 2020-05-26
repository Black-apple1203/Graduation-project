<?php
/**
 * 合并加载JS和CSS文件
 *
 * @author brivio
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class LoadTag {
    protected $jm;
    protected $options = array();
    function __construct($options) {
        $this->options = $options;
        import('Common.ORG.JSMin');
        $this->jm = new \JSMin();
    }
    /*
     * 生成默认JS文件(根据当前模型类名称)
    */
    public function def(){
    	$this->js(array('href'=>'__STATIC__/js/'.MODULE_NAME.'/'.__MODULE__.'.js'));
    }
    public function js() {
        $path = QSCMS_DATA_PATH . 'static/' . md5($this->options['href']) . '.js';
        $statics_url = C('qscms_statics_url') ? C('qscms_statics_url') : './static';
        if (!is_file($path)) {
            //静态资源地址
            $files = explode(',', $this->options['href']);
            $content = '';
            foreach ($files as $val) {
                $val = str_replace('__STATIC__', $statics_url, $val);
                $content.=file_get_contents($val);
            }
            file_put_contents($path, $this->jm->minify($content));
        }
        echo ( '<script type="text/javascript" src="' . __ROOT__ . '/data/static/' . md5($this->options['href']) . '.js?"></script>');
    }
    /**
     * [category 生成项目所需枚举类js缓存]
     * @return [js]                  [js文件]
     */
    public function category(){
        $path = QSCMS_DATA_PATH . 'static/' . md5('cache_classify') . $cache . '.js';
        $statics_url = C('qscms_statics_url') ? C('qscms_statics_url') : './static';
        if (!is_file($path)) {
            //静态资源
            $cates['QS_city'] = D('CategoryDistrict')->get_district_cache('all');
            $cates['QS_jobs'] = D('CategoryJobs')->get_jobs_cache('all');
            $cates['QS_major'] = D('CategoryMajor')->get_major_cache('all');
            if(false === $this->apply = F('apply_list')) $this->apply = D('Apply')->apply_cache();
            if($this->apply['Mall']){
                $cates['QS_shop'] = D('Mall/MallCategory')->get_mall_cache('all');
            }
            $content = '';
            foreach ($cates as $key => $cate) {
                $content.= "var {$key}_parent=new Array({$this->assembly($cate[0])});";
                $content.="var {$key}=new Array();";
                foreach ($cate[0] as $_key=>$val) {
                    $content.="{$key}[{$_key}]={$this->assembly($cate[$_key],'`','')};";
                    if($key == 'QS_jobs' && $cate[$_key]){
                        foreach ($cate[$_key] as $skey=>$sval) {
                            $content.="{$key}[{$skey}]={$this->assembly($cate[$skey],'`','')};";
                        }
                    }
                    if($key == 'QS_city' && $cate[$_key]){
                        foreach ($cate[$_key] as $skey=>$sval) {
                            $content.="{$key}[{$skey}]={$this->assembly($cate[$skey],'`','')};";
                            if($cate[$skey]){
                                foreach ($cate[$skey] as $skey4=>$sval4) {
                                    $content.="{$key}[{$skey4}]={$this->assembly($cate[$skey4],'`','')};";
                                    if($cate[$skey4]){
                                        foreach ($cate[$skey4] as $skey5=>$sval5) {
                                            $content.="{$key}[{$skey5}]={$this->assembly($cate[$skey5],'`','')};";
                                            if($cate[$skey5]){
                                                foreach ($cate[$skey5] as $skey6=>$sval6) {
                                                    $content.="{$key}[{$skey6}]={$this->assembly($cate[$skey6],'`','')};";
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if(false === $spell['QS_jobs_spell'] = F('jobs_custom_cate')) $spell['QS_jobs_spell'] = D('CategoryJobs')->custom_jobs_cache();
            if(false === $spell['QS_city_spell'] = F('district_custom_cate')) $spell['QS_city_spell'] = D('CategoryDistrict')->custom_district_cache();
            foreach ($spell as $key => $cate) {
                $content.= "var {$key}_parent=new Array({$this->spell_assembly($cate[0])});";
                $content.="var {$key}=new Array();";
                foreach ($cate[0] as $_key=>$val) {
                    $content.="{$key}['{$val['spell']}']={$this->spell_assembly($cate[$_key],'`','')};";
                    if($cate[$_key]){
                        foreach ($cate[$_key] as $skey=>$sval) {
                            $content.="{$key}['{$sval['spell']}']={$this->spell_assembly($cate[$skey],'`','')};";
                            if($cate[$skey]){
                                foreach ($cate[$skey] as $skey4=>$sval4) {
                                    $content.="{$key}['{$sval4['spell']}']={$this->spell_assembly($cate[$skey4],'`','')};";
                                    if($cate[$skey4]){
                                        foreach ($cate[$skey4] as $skey5=>$sval5) {
                                            $content.="{$key}['{$sval5['spell']}']={$this->spell_assembly($cate[$skey5],'`','')};";
                                            if($cate[$skey5]){
                                                foreach ($cate[$skey5] as $skey6=>$sval6) {
                                                    $content.="{$key}['{$sval6['spell']}']={$this->spell_assembly($cate[$skey6],'`','')};";
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $category = D('Category')->get_category_cache();
            foreach ($category as $key => $val) {
                $arr = array();
                foreach ($val as $_key=>$_val) {
                    $arr[] = '"'.$_key.','.$_val.'"';
                }
                $arr = implode(',',$arr);
                $content.="var {$key}=new Array({$arr});";
            }
            file_put_contents($path,$this->jm->minify($content));
        }
        echo ( '<script type="text/javascript" src="' . __ROOT__ . '/data/static/' . md5('cache_classify') . $cache . '.js?"></script>');
    }
    /**
     * [spell_assembly 数组转字符串]
     * @param  [array]     $data    [被转换的数组]
     * @param  string      $p       [间隔字符]
     * @return [string]             [处理结果]
     */
    public function spell_assembly($data,$p=',',$s='"'){
        foreach ($data as $key=>$val) {
            $arr[] = $s.$val['spell'].','.$val['categoryname'].$s;
        }
        $arr = implode($p,$arr);
        if(!$s) return '"'.$arr.'"';
        return $arr;
    }
    /**
     * [assembly 数组转字符串]
     * @param  [array]     $data    [被转换的数组]
     * @param  string      $p       [间隔字符]
     * @return [string]             [处理结果]
     */
    public function assembly($data,$p=',',$s='"'){
        foreach ($data as $key=>$val) {
            $arr[] = $s.$key.','.$val.$s;
        }
        $arr = implode($p,$arr);
        if(!$s) return '"'.$arr.'"';
        return $arr;
    }
}