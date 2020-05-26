<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class ImController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Config');
        $this->_name = 'config';
    }
	//新增首页快捷语列表
	public function index(){
		$count=M('ImText')->count();
		$pager = pager($count, $pagesize);
    	$imtext=M('ImText')->limit($pager->firstRow.','.$pager->listRows)->order('id desc')->select();
		$page = $pager->fshow(true);
        $this->assign("page", $page);
		$this->assign("list", $imtext);
		$this->display();
    }
}
?>