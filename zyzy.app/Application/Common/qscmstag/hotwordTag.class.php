<?php
/**
 * 热门关键词
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class hotwordTag {
    protected $params = array();
    protected $map = array();
    protected $order;
    protected $limit;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '显示数目'          =>  'row',
            '标题长度'          =>  'titlelen',
            '开始位置'          =>  'start',
            '填补字符'          =>  'dot'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->limit>20 && $this->limit=20;
        $this->order = 'w_hot desc';
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):5;
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
        $result = M('Hotword')->order($this->order)->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['w_word']=cut_str($row['w_word'],$this->params['titlelen'],0,$this->params['dot']);
            $row['w_word_code'] = $row['w_word'];
            $list[] = $row;
        }
        return $list;
    }
}