<?php
/**
 * 页面信息
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class pageinfoTag {
    protected $params = array();
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '分类id'            =>  'id',
            '调用名称'          =>  'alias'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->params['alias'] = isset($this->params['alias'])?trim($this->params['alias']):'QS_index';
        $this->params['id'] = isset($this->params['id'])?intval($this->params['id']):0;
    }
    public function run(){
        if ($this->params['alias']=="QS_newslist" && $this->params['id'])
        {
            $info = M('ArticleCategory')->where(array('id'=>$this->params['id']))->find();
        }
        else
        {
            $info = M('page')->where(array('alias'=>array('eq',$this->params['alias'])))->find();
        }

        return $info;
    }
}