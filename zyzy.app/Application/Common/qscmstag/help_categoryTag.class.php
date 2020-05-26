<?php
/**
 * 帮助分类
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class help_categoryTag {
    protected $params = array();
    protected $map = array();
    protected $order;
    protected $limit;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '名称长度'          =>  'titlelen',
            '填补字符'          =>  'dot',
            '大类'              =>  'parentid',
            '小类'              =>  'typeid',
            '页面'              =>  'showname',
            '显示数目'          =>  'row'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        if(isset($this->params['typeid']) && intval($this->params['typeid'])>0){
            $this->map['id'] = array('eq',intval($this->params['typeid']));
        }
        if(isset($this->params['parentid'])){
            $this->map['parentid'] = array('eq',intval($this->params['parentid']));
        }
        $this->order = 'category_order DESC,id asc';
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
        $this->params['showname']=isset($this->params['showname'])?$this->params['showname']:"QS_helplist";
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
    }
    public function run(){
        $select = M('HelpCategory');
        if($this->map){
            $select = $select->where($this->map);
        }
        $result = $select->order($this->order)->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['url']=url_rewrite($this->params['showname'],array('id'=>$row['id'],'parentid'=>$row['parentid']));
            $row['title_']=$row['categoryname'];
            $row['title']=cut_str($row['categoryname'],$this->params['titlelen'],0,$this->params['dot']);
            $list[] = $row;
        }
        if (isset($this->params['typeid']))
        {
            $return = $list[0];
        }else{
            $return = $list;
        }
        return $return;
    }
}