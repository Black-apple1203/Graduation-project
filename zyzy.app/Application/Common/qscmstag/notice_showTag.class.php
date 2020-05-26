<?php
/**
 * 公告详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class notice_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'公告id'			=>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['is_display'] = array('eq',1);
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
    }
    public function run(){
        $val = M('Notice')->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $category = D('NoticeCategory')->find($val['type_id']);
        $val['type_cn'] = $category['categoryname'];
        $val['content']=htmlspecialchars_decode($val['content'],ENT_QUOTES);
        if ($val['seo_keywords']=="")
        {
        $val['seo_keywords']=$val['title'];
        }
        if ($val['seo_description']=="")
        {
        $val['seo_description']=cut_str(strip_tags($val['content']),60,0,"");
        }
        return $val;
    }
}