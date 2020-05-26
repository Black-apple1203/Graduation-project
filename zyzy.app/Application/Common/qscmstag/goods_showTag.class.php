<?php
/**
 * 商品详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class goods_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'商品id'			=>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
    }
    public function run(){
        $val = M('MallGoods')->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['goods_img'] = $val['goods_img']?attach($val['goods_img'],'mall'):attach($val['goods_img'],'resource');
        $val['content']=htmlspecialchars_decode($val['content'],ENT_QUOTES);
        return $val;
    }
}