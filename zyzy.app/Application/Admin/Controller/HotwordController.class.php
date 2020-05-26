<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class HotwordController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Hotword');
    }
    public function index(){
        $this->_name = 'Hotword';
        parent::_list($this->_mod,array(),'w_hot desc');
        $this->display();
    }
}