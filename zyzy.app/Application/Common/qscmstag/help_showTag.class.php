<?php
/**
 * 帮助详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class help_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'id'			    =>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
    }
    public function run(){
        $val = M('Help')->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['keywords']=$val['title'];
        $val['description']=cut_str(strip_tags($val['content']),60,0,"");
        $val['content']=htmlspecialchars_decode($val['content'],ENT_QUOTES);
        return $val;
    }
}