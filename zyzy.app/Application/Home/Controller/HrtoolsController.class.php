<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class HrtoolsController extends FrontendController{
	public function _initialize(){
        parent::_initialize();
    }
	public function index(){
		//添加meta的链接
		$canonical=C('qscms_site_domain').$_SERVER['REQUEST_URI'];
		$this->assign('canonical',$canonical);
		//end
		$this->display();
	}
	public function hrtools_list(){
		//添加meta的链接
		$canonical=C('qscms_site_domain').$_SERVER['REQUEST_URI'];
		$this->assign('canonical',$canonical);
		//end
		$this->display();
	}
}
?>