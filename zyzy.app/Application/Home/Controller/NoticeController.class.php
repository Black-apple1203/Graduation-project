<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class NoticeController extends FrontendController{
	public function _initialize(){
        parent::_initialize();
    }
	public function index(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'Notice','a'=>'index')));
		}
		C('TOKEN_ON',true);
		$this->display();
	}
	public function notice_show(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'Notice','a'=>'show','params'=>'id='.intval($_GET['id']))));
		}
		$this->display();
	}
}
?>