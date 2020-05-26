<?php
/**
 * 说明页详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class explain_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'说明页id'			=>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
    }
    public function run(){
        $val = M('Explain')->where($this->map)->find();
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
        $val['seo_description']=cut_str(strip_tags($val['content']),60,0,"");
        }
        return $val;
    }
}