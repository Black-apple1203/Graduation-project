<?php
/**
 * 资讯分类
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class news_categoryTag {
    protected $params = array();
    protected $map = array();
    protected $limit;
    protected $order;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '显示数目'          =>  'row',
            '名称长度'          =>  'titlelen',
            '开始位置'          =>  'start',
            '填补字符'          =>  'dot',
            '资讯大类'          =>  'parentid',
            '资讯小类'          =>  'type_id',
            '排序'              =>  'displayorder',
            '页面'              =>  'showname'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        if(isset($this->params['parentid'])){
            $this->map['parentid'] = array('eq',intval($this->params['parentid']));
        }
        if(isset($this->params['type_id']) && intval($this->params['type_id'])>0){
            $this->map['id'] = array('eq',intval($this->params['type_id']));
        }
        $displayorder = isset($this->params['displayorder'])?explode(':',$this->params['displayorder']):array('category_order','desc');
        $this->order = $displayorder[0].' '.$displayorder[1];
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->limit>20 && $this->limit=20;
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
        $this->params['showname']=isset($this->params['showname'])?$this->params['showname']:'QS_newslist';
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
        $select = M('ArticleCategory');
        if($this->map){
            $select = $select->where($this->map);
        }
        $result = $select->order($this->order)->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['url']=url_rewrite($this->params['showname'],array('id'=>$row['id']));
            $row['title_']=$row['categoryname'];
            $row['title']=cut_str($row['categoryname'],$this->params['titlelen'],0,$this->params['dot']);
            $list[] = $row;
        }
        if(isset($this->params['type_id'])){
            $return = $list[0];
        }else{
            $return = $list;
        }
        return $return;
    }
}