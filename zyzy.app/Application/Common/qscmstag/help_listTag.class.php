<?php
/**
 * 帮助列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class help_listTag {
    protected $params = array();
    protected $map = array();
    protected $limit;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '显示数目'          =>  'row',
            '大类'              =>  'parentid',
            '小类'              =>  'type_id',
            '标题长度'          =>  'titlelen',
            '摘要长度'          =>  'infolen',
            '开始位置'          =>  'start',
            '填补字符'          =>  'dot',
            '关键字'            =>  'key',
            '分页显示'          =>  'paged',
            '页面'              =>  'showname',
            '列表页'            =>  'listpage'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        if(isset($this->params['parentid']) && intval($this->params['parentid'])>0){
            $this->map['parentid'] = array('eq',intval($this->params['parentid']));
        }
        if(isset($this->params['type_id']) && intval($this->params['type_id'])>0){
            $this->map['type_id'] = array('eq',intval($this->params['type_id']));
        }
        if(isset($this->params['key']) && trim($this->params['key'])<>''){
            $this->params['key'] = urldecode(urldecode($this->params['key']));
            $this->map['title'] = array('like','%'.trim($this->params['key']).'%');
        }
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->limit>20 && $this->limit=20;
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
        $this->params['infolen']=isset($this->params['infolen'])?intval($this->params['infolen']):0;
        $this->params['showname']=isset($this->params['showname'])?$this->params['showname']:'QS_helpshow';
        $this->params['listpage']=isset($this->params['listpage'])?$this->params['listpage']:'QS_helplist';
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
        if($this->params['paged']){
            $count = M('Help')->where($this->map);
            $total = $count->count();
            $pager = pager($total, $this->limit);
            $pager->showname = $this->params['listpage'];
            $page = $pager->fshow();
            $this->params['start']>0 && $pager->firstRow = $this->params['start'];
            $this->limit = $pager->firstRow.','.$pager->listRows;
        }else{
            $this->limit = $this->params['start'].','.$this->limit;
            $total = 0;
            $page = '';
        }
        
        $select = M('Help');
        if($this->map){
            $select = $select->where($this->map);
        }
        $result = $select->limit($this->limit)->order('ordid desc,addtime desc')->select();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['title_']=$row['title'];
            $row['title']=cut_str($row['title'],$this->params['titlelen'],0,$this->params['dot']);
            $row['url'] = url_rewrite($this->params['showname'],array('id'=>$row['id']));
            $row['content']=str_replace('&nbsp;','',$row['content']);
            $row['briefly_']=strip_tags($row['content']);
            if ($this->params['infolen']>0)
            {
            $row['briefly']=cut_str(strip_tags($row['content']),$this->params['infolen'],0,$this->params['dot']);
            }
            $list[] = $row;
        }
        $return['page'] = $page;
        $return['total'] = $total;
        $return['list'] = $list;
        return $return;
    }
}