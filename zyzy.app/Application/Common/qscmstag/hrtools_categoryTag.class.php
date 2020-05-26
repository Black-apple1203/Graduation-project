<?php
/**
 * hr工具箱分类
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class hrtools_categoryTag {
    protected $params = array();
    protected $map = array();
    protected $order;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '标题长度'          =>  'titlelen',
            '填补字符'          =>  'dot',
            '分类id'            =>  'c_id'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        isset($this->params['c_id']) && $this->map['c_id'] = array('eq',intval($this->params['c_id']));
        $this->order = 'c_order DESC,c_id ASC';
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
        $select = M('HrtoolsCategory');
        if($this->map){
            $select = $select->where($this->map);
        }
        $result = $select->order($this->order)->select();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['img'] = $row['c_img']?attach($row['c_img'],'hrtools_img'):attach($row['c_id'].'.jpg','resource/hrtools_img');
            $row['url'] = url_rewrite('QS_hrtoolslist',array('id'=>$row['c_id']));
            $list[] = $row;
        }
        if($this->map['c_id']){
            return $list[0];
        }else{
            return $list;
        }
    }
}