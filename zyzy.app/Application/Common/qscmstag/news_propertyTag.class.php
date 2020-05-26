<?php
/**
 * 资讯属性
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class news_propertyTag {
    protected $params = array();
    protected $map = array();
    protected $order;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '名称长度'          =>  'titlelen',
            '填补字符'          =>  'dot',
            '排序'              =>  'displayorder',
            '分类id'            =>  'id'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        if(isset($this->params['id']) && intval($this->params['id'])>0){
            $this->map['id'] = array('eq',intval($this->params['id']));
        }
        $displayorder = isset($this->params['displayorder'])?explode(':',$this->params['displayorder']):array('category_order','desc');
        $this->order = $displayorder[0].' '.$displayorder[1].',id desc';
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
        $select = M('ArticleProperty');
        if($this->map){
            $list = $select->where($this->map)->find();
        }else{
            $list = $select->select();
        }
        return $list;
    }
}