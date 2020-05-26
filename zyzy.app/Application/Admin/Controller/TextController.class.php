<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class TextController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Text');
    }
}
?>