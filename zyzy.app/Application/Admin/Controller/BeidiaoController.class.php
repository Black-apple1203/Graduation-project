<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class BeidiaoController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_name = 'Config';
    }

    public function index(){
    	parent::_edit();
    	$this->display();
    }

    //背景调查
    public function beidiao_edit(){
        if(IS_POST){
            foreach (I('post.') as $key => $val) {
                $val = is_array($val) ? serialize(htmlspecialchars_decode($val,ENT_QUOTES)) : htmlspecialchars_decode($val,ENT_QUOTES);
                D('Config')->where(array('name' => $key))->save(array('value' => $val));
            }
        }
        $this->_edit();
        $this->display();
    }
}