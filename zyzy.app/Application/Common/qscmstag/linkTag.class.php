<?php
/**
 * 友情链接
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class linkTag {
    protected $params = array();
    protected $map = array();
    protected $limit;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '显示数目'          =>  'row',
            '开始位置'          =>  'start',
            '文字长度'          =>  'len',
            '填补字符'          =>  'dot',
            '类型'              =>  'linktype',
            '调用名称'          =>  'alias'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->map['display'] = array('eq',1);
        if(isset($this->params['alias'])){
            $this->map['alias'] = array('eq',trim($this->params['alias']));
        }
        if(isset($this->params['type_id']) && intval($this->params['type_id'])>0){
            $this->map['type_id'] = array('eq',intval($this->params['type_id']));
        }
        $this->limit = isset($this->params['row'])?intval($this->params['row']):60;
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
        $this->params['len']=isset($this->params['len'])?intval($this->params['len']):8;
        $this->params['linktype']=isset($this->params['linktype'])?intval($this->params['linktype']):1;
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
        if($this->params['linktype']==1){
            $this->map['link_logo'] = array('eq','');
        }else{
            $this->map['link_logo'] = array('neq','');
        }
        $this->limit = $this->params['start'].' '.$this->limit;
    }
    public function run(){
        $result = M('Link')->where($this->map)->order('show_order desc')->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['title_']=$row['link_name'];
            $row['title']=cut_str($row['link_name'],$this->params['len'],0,$this->params['dot']);
            $list[] = $row;
        }
        return $list;
    }
}