<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class QiniuController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Config');
        $this->_name = 'config';
    }
}
?>