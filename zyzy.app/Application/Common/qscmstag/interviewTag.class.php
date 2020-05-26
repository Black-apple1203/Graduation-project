<?php
/**
 * 资讯列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class interviewTag {
    protected $params = array();
    protected $map = array();
    protected $order;
    protected $limit;
    function __construct($options) {
        $array = array(
            '列表名'            =>  'listname',
            '显示数目'          =>  'row',
            '图片'              =>  'img',
            '标题长度'          =>  'titlelen',
            '摘要长度'          =>  'infolen',
            '开始位置'          =>  'start',
            '填补字符'          =>  'dot',
            '日期范围'          =>  'settr',
            '排序'              =>  'displayorder',
            '关键字'            =>  'key',
            '分页显示'          =>  'paged',
            '页面'              =>  'showname',
            '列表页'            =>  'listpage'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->map['is_display'] = array('eq',1);
        if(isset($this->params['img']) && intval($this->params['img'])>0){
            $this->map['small_img'] = array('neq','');
        }
        if(isset($this->params['settr']) && intval($this->params['settr'])>0){
            $this->map['addtime'] = array('gt',strtotime("-".intval($this->params['settr'])." day"));
        }
		//分站信息调取
		if(C('subsite_info') && C('subsite_info.s_id')!=0){
			  $this->map['subsite_id'] = C('subsite_info.s_id');
		}
		//end
        if(isset($this->params['key']) && trim($this->params['key'])<>''){
            $this->params['key'] = urldecode(urldecode($this->params['key']));
            $this->map['title'] = array('like','%'.trim($this->params['key']).'%');
        }
        $displayorder = isset($this->params['displayorder'])?explode(':',$this->params['displayorder']):array('article_order','desc');
        $this->order = $displayorder[0].' '.$displayorder[1].',addtime desc';
        $this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->limit>20 && $this->limit=20;
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
        $this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):35;
        $this->params['infolen']=isset($this->params['infolen'])?intval($this->params['infolen']):0;
        $this->params['showname']=isset($this->params['showname'])?$this->params['showname']:'QS_interview_show';
        $this->params['listpage']=isset($this->params['listpage'])?$this->params['listpage']:'QS_interview_list';
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
        if($this->params['paged']){
            $count = M('Interview')->where($this->map);
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
        $result = M('Interview')->where($this->map)->order($this->order)->limit($this->limit)->select();
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
                if(strpos($row['is_url'],'http') !== false){
                    $row['url'] =$row['is_url'];  
                }else{
                    $row['url'] ='http://'.$row['is_url'];
                }
            }
            else
            {
                $row['url'] = url_rewrite($this->params['showname'],array('id'=>$row['id']));
            }
            $row['content']=str_replace('&nbsp;','',$row['content']);
            $row['briefly_']=strip_tags(htmlspecialchars_decode($row['content'],ENT_QUOTES));
                if ($this->params['infolen']>0)
                {
                    $row['briefly']=cut_str(strip_tags(htmlspecialchars_decode($row['content'],ENT_QUOTES)),$this->params['infolen'],0,$this->params['dot']);
                }else{
                    $row['briefly']=$row['briefly_'];
                }
            $row['img']=$row['small_img']?attach($row['small_img'],'images'):attach('no_img_news.png','resource');
            $row['isimg']=$row['small_img']; // 不带路径，判断是否有缩略图，在news.htm页面用到

            $list[] = $row;
        }
        //dump($page);die;
        $return['page'] = $page;
        $return['total'] = $total;
        $return['list'] = $list;
        $return['breadcrumb'] = $breadcrumb;
        return $return;
    }
}