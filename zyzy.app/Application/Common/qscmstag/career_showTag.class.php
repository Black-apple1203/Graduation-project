<?php
/**
 * 资讯详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class career_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'招考id'			=>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['is_display'] = array('eq',1);
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
    }
    public function run(){
        $val = M('Career')->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['content']=htmlspecialchars_decode($val['content'],ENT_QUOTES);
        if ($val['seo_keywords']=="")
        {
        $val['seo_keywords']=$val['title'];
        }
        if ($val['seo_description']=="")
        {
        $val['seo_description']=strip_tags($val['content']);
        $val['seo_description']=trim($val['seo_description']);
        $val['seo_description']=cut_str($val['seo_description'],60,0,"");
        }
        $val['img'] = $val['small_img']?attach($val['small_img'],'images'):attach('no_img_news.png','resource');
        $fields = 'id,title';
        $list = M('Career')->where($where)->order('article_order desc, addtime desc')->field($fields)->select();
        $current = -1;
        foreach ($list as $k => $v){
            if ($v['id'] == $val['id']){
                $current = $k;
                break;
            }
        }
        $prev = $current>-1 && !empty($list[$current-1]) ? $list[$current-1] : 0;
        if(!$prev){
            $val['prev'] = 0;
        }else{
            $val['prev'] = 1;
            $val['prev_title'] = $prev['title'];
            $val['prev_url'] = url_rewrite("QS_career_show",array('id'=>$prev['id']));
        }
        $next = $current>-1 && !empty($list[$current+1]) ? $list[$current+1] : 0;
        if(!$next){
            $val['next'] = 0;
        }else{
            $val['next'] = 1;
            $val['next_title'] = $next['title'];
            $val['next_url'] = url_rewrite("QS_career_show",array('id'=>$next['id']));
        }
        return $val;
    }
}