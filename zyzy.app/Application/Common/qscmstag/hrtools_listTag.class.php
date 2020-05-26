<?php
/**
 * hr工具箱列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class hrtools_listTag {
    protected $params = array();
    protected $map = array();
    protected $limit;
    protected $order;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '显示数目'          =>  'row',
            '标题长度'          =>  'titlelen',
            '开始位置'          =>  'start',
            '填补字符'          =>  'dot',
            '分类id'            =>  'h_typeid'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        if(isset($this->params['h_typeid']) && intval($this->params['h_typeid'])>0){
            $this->map['h_typeid'] = array('eq',intval($this->params['h_typeid']));
        }
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->limit>20 && $this->limit=20;
        $this->order = 'h_order DESC,h_id ASC';
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
        $this->limit = $this->params['start'].' '.$this->limit;
    }
    public function run(){
        $select = M('Hrtools');
        if($this->map){
            $select = $select->where($this->map);
        }
        $result = $select->order($this->order)->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            if ($row['h_strong']=="1")
            {
            $row['h_filename']="<strong>{$row['h_filename']}</strong>";
            }
            if ($row['h_color'])
            {
            $row['h_filename']="<span style=\"color:{$row['h_color']}\">{$row['h_filename']}</span>";
            }
            $row['h_fileurl'] = U('Home/download/hrtools',array('id'=>$row['h_id']));
            $list[] = $row;
        }
        return $list;
    }
}