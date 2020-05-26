<?php
/**
 * 说明页列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class explain_listTag {
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
            '填补字符'          =>  'dot',
            '排序'              =>  'displayorder',
            '分类id'            =>  'type_id',
            '页面'              =>  'showname'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->map['is_display'] = array('eq',1);
        if(isset($this->params['type_id']) && intval($this->params['type_id'])>0){
            $this->map['type_id'] = array('eq',intval($this->params['type_id']));
        }
		//分站信息调取
		if(C('subsite_info')){
			 $this->map['subsite_id'] = C('subsite_info.s_id');
		}
		//end
        $displayorder = isset($this->params['displayorder'])?explode(':',$this->params['displayorder']):array('show_order','desc');
        $this->order = $displayorder[0].' '.$displayorder[1].',id desc';
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->limit>20 && $this->limit=20;
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
        $this->params['showname']=isset($this->params['showname'])?$this->params['showname']:'QS_explainshow';
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
        $this->limit = $this->params['start'].','.$this->limit;
    }
    public function run(){
        $result = M('Explain')->where($this->map)->order($this->order)->limit($this->limit)->select();
		//echo M('Explain')->getLastsql();
        $list = array();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['title_']=$row['title'];
            $style_color=$row['tit_color']?"color:".$row['tit_color'].";":'';
            $style_font=$row['tit_b']=="1"?"font-weight:bold;":'';
            $row['title']=cut_str($row['title'],$this->params['titlelen'],0,$this->params['dot']);
            if ($style_color || $style_font)$row['title']="<span style=".$style_color.$style_font.">".$row['title']."</span>";
            if (!empty($row['is_url']) && $row['is_url']!='http://')
            {
            $row['url']= $row['is_url'];
            }
            else
            {
            $row['url'] = url_rewrite($this->params['showname'],array('id'=>$row['id'],'type_id'=>$this->params['type_id']));
            }
            $list[] = $row;
        }
        return $list;
    }
}