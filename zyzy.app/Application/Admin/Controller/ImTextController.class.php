<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ImTextController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('ImText');
		 $this->_name = 'ImText';
    }
}
?>