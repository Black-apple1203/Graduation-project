<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class CloudController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Config');
        $this->_name = 'config';
    }
}
?>