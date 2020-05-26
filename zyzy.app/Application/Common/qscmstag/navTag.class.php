<?php
/**
 * 导航
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class navTag {
    protected $params = array();
    protected $limit;
    protected $alias;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '显示数目'          =>  'row',
            '调用名称'          =>  'alias'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->alias = isset($this->params['alias'])?trim($this->params['alias']):'QS_top';
    }
    public function run(){
        $nav = F('nav_list');
        if($nav===false){
            $nav = D('Navigation')->nav_cache();
        }
        $list = $nav[$this->alias];
        $return = array_slice($list,0,$this->limit);
        foreach ($return as $key => $value) {
            $arr = $value;
            if($value['url']!='' && $value['urltype'] == 1){
                $arr['url'] = $value['url'];
            }else if($value['type_id']!=''){
                $arr['url'] = url_rewrite($value['pagealias'],array('id'=>$value['type_id']));
            }else{
                $arr['url'] = url_rewrite($value['pagealias']);
            }
            if($value['color']!=''){
                $arr['title'] = "<font color='".$value['color']."'>".$value['title']."</font>";
            }
            $return[$key] = $arr;
        }
        return $return;
    }
}